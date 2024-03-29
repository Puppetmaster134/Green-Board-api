<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require './vendor/autoload.php';
require './vendor/slim/extras/Slim/Extras/Log/DateTimeFileWriter.php';


$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

//create connection for database
$config['db']['host']   = getenv("RDS_HOSTNAME");
$config['db']['user']   = getenv("RDS_USERNAME");
$config['db']['pass']   = getenv("RDS_PASSWORD");
$config['db']['dbname'] = getenv("RDS_DB_NAME");

//start a new slim application
$app = new \Slim\App(["settings" => $config]);
	
$container = $app->getContainer();

//setup the PDO
$container['db'] = function ($c)
{
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'], $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$container['logger'] = function($c) 
{
	$logDir = getenv("LOG_DIR");
    $logger = new \Monolog\Logger('[REQUEST]');
    $file_handler = new \Monolog\Handler\StreamHandler($logDir);
    $logger->pushHandler($file_handler);
    return $logger;
};

$checkProxyHeaders = true; // Note: Never trust the IP address for security processes!
$trustedProxies = ['10.0.0.1', '10.0.0.2']; // Note: Never trust the IP address for security processes!
$app->add(new RKA\Middleware\IpAddress($checkProxyHeaders, $trustedProxies));





function isKeyValid($pdo,$key)
{
    $sql = "SELECT COUNT(*) count FROM user WHERE api_key=:key LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':key'=>$key));
    $result = $stmt->fetch();
    if($result['count'] > 0)
	{
		return true;
	}
	return false;
}

function accountExists($pdo, &$response, $fields)
{
	$responseObj = array();
	$args = array();

	/*****
		Check username
	*****/
	$sql = "SELECT COUNT(*) count FROM user WHERE username=:username LIMIT 1";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(':username'=>$fields['username']));
	$result = $stmt->fetch();

	if($result['count'] > 0)
	{
		$args["username"] = "Username is already registered.";
	}


	/*****
		Check email
	*****/

	$sql = "SELECT COUNT(*) count FROM user WHERE email=:email LIMIT 1";
	$stmt = $pdo->prepare($sql);
	$stmt->execute(array(':email'=>$fields['email']));
	$result = $stmt->fetch();

	if($result['count'] > 0)
	{
		$args["email"] = "Email is already registered.";
	}

	/******
		Check Facebook ID
	******/

	if(isset($fields['fbid']))
	{
		$sql = "SELECT COUNT(*) count FROM user WHERE facebookId=:fbid LIMIT 1";
		$stmt = $pdo->prepare($sql);
		$stmt->execute(array(':fbid'=>$fields['fbid']));
		$result = $stmt->fetch();

		if($result['count'] > 0)
		{
			$args["fbid"] = "FacebookID is already registered.";
		}
	}
	/**/
	if(sizeof($args) > 0)
	{
		$args['error'] = "Could not register account.";
		$responseObj['args'] = $args;
		$response->getBody()->write(json_encode($responseObj));
		return true;
	}

	return false;
}

$app->get('/RegisterUser/', function (Request $request, Response $response)
{
	$params = $request->getQueryParams();
	if(!accountExists($this->db,$response,array("username"=>$params['username'],"email"=>$params['email'])))
	{
		$sql = "INSERT INTO user (username, password, email, api_key) VALUES(:username, :password, :email, :api_key)";
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array(':username'=>$params['username'], ':password'=>$params['password'], ':email'=>$params['email'], ':api_key'=>md5($params['email'])));
		$responseObj = array("args"=>array("success"=>"Registered Successfully"));
		$response->getBody()->write(json_encode($responseObj));
	}
	
	$this->logger->addInfo("[" . $request->getAttribute('ip_address') . "] Registration(GB) request - Response: " . $response->getBody());
	return $response;
});

$app->get('/RegisterUserWithFB/', function (Request $request, Response $response)
{
	$params = $request->getQueryParams();
	$fields = array("username"=>$params['username'],"email"=>$params['email'],"fbid"=>$params['fbid']);

	if(!accountExists($this->db,$response,array("username"=>$params['username'],"email"=>$params['email'],"fbid"=>$params['fbid'])))
	{
		$sql = "INSERT INTO user (username, email, facebookId, api_key) VALUES(:username, :email, :fbid, :api_key)";
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array(':username'=>$params['username'],':email'=>$params['email'], ':fbid'=>$params['fbid'], ':api_key'=>md5($params['email'])));
		$responseObj = array("args"=>array("success"=>"Registered Successfully"));
		$response->getBody()->write(json_encode($responseObj));
	}

	
	$this->logger->addInfo("[" . $request->getAttribute('ip_address') . "] Registration(FB) request - Response: " . $response->getBody());
	return $response;

});

$app->get('/Login/', function(Request $request, Response $response)
{
	
	$params = $request->getQueryParams();
	$log;
	$sql = "SELECT username,api_key FROM user WHERE username=:username AND password=:password LIMIT 1";
	$stmt = $this->db->prepare($sql);
	$stmt->execute(array(':username'=>$params['username'],':password'=>$params['password']));
	$result = $stmt->fetch();
	$responseObj = array();
	
	if(!isset($result['api_key']))
	{
		$responseObj['args'] = array("error"=>"Invalid login credentials.");
	}
	else
	{
		$responseObj['args'] = array("success"=>"Login successful.");
		$responseObj['result'] = $result;
	}

	
	$this->logger->addInfo("[" . $request->getAttribute('ip_address') . "] GetTrailById endpoint request - Parameters:" . json_encode($params) . ", Response: " . json_encode($responseObj['args']));
	$response->getBody()->write(json_encode($responseObj));
	return $response;

});


$app->get('/LoginWithFB/', function(Request $request, Response $response)
{
	$params = $request->getQueryParams();
	
	$sql = "SELECT username,api_key FROM user WHERE facebookId = :fbid LIMIT 1";
	$stmt = $this->db->prepare($sql);
	$stmt->execute(array(':fbid'=>$params['fbid']));
	$result = $stmt->fetch();
	$responseObj = array();
	
	if(!isset($result['api_key']))
	{
		$responseObj['args'] = array("error"=>"Invalid login credentials.");
	}
	else
	{
		$responseObj['args'] = array("success"=>"Login successful.");
		$responseObj['result'] = $result;
	}

	$this->logger->addInfo("[" . $request->getAttribute('ip_address') . "] LoginWithFB endpoint request - Parameters:" . json_encode($params) . ", Response: " . json_encode($responseObj['args']));
	$response->getBody()->write(json_encode($responseObj));
	return $response;
});


$app->get('/GetTrailById/', function (Request $request, Response $response)
{
	$params = $request->getQueryParams();
	$responseObj = array();
	
	if(isKeyValid($this->db,$params['key']))
	{
		if(isset($params['id']))
		{
			$sql = "SELECT * FROM trail WHERE id=:id LIMIT 1";
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array(':id'=> $params['id']));
			$result = $stmt->fetch();

			if(!empty($result))
			{
				$responseObj['args'] = array("success"=>"Trail retrieved successfully.");
				$responseObj['result'] = $result;
			}
			else
			{
				$responseObj['args'] = array("error"=>"Trail " . $params['id'] . " does not exist.");
			}
		}
		else
		{
			$responseObj['args'] = array("error"=>"No trail id specified.");
		}
	}
	else
	{
		$responseObj['args'] = array("error"=>"Invalid API key.");
	}

	$this->logger->addInfo("[" . $request->getAttribute('ip_address') . "] GetTrailById endpoint request - Parameters:" . json_encode($params) . ", Response: " . json_encode($responseObj['args']));
	$response->getBody()->write(json_encode($responseObj));
    return $response;

});

$app->get('/WriteTrailToDB/', function (Request $request, Response $response)
{
    $params = $request->getQueryParams();
	$responseObj = array();
    if(isKeyValid($this->db,$params['key']))
    {
		
		if(isset($params['trailName']) && isset($params['lat']) && isset($params['lng']) && isset($params['trailObj']))
		{
			
			$namedParameters = array(
				':trailName'=>$params['trailName'],
				':trailInfo'=>(isset($params['trailInfo'])) ? $params['trailInfo'] : "Empty description.",
				':lat'=>$params['lat'],
				':lng'=>$params['lng'],
				':trailObj'=>$params['trailObj']
			);
			$sql = "INSERT INTO trail (trailName,trailInfo,lat,lng,trailObj) VALUES(:trailName,:trailInfo,:lat,:lng,:trailObj)";
			$stmt = $this->db->prepare($sql);
			$stmt->execute($namedParameters);
			$responseObj['args'] = array("success"=>$params['trailName'] . " added successfully. (" . $params['lat'] . "," . $params['lng'] . "): " . $params['trailObj']);
			
		}
		else
		{
			$responseObj['args'] = array("error"=>"Invalid parameters.");
		}
		
    }
	else
	{
		$responseObj['args'] = array("error"=>"Invalid API key.");
	}

	$this->logger->addInfo("[" . $request->getAttribute('ip_address') . "] WriteTrailToDB endpoint request - Parameters:" . json_encode($params) . ", Response: " . json_encode($responseObj['args']));
	$response->getBody()->write(json_encode($responseObj));
    return $response;
});


$app->get('/GetTrailInArea/', function (Request $request, Response $response)
{
    $params = $request->getQueryParams();
	$responseObj = array();
    if(isKeyValid($this->db,$params['key']))
    {
		if(isset($params['minLat']) && isset($params['minLng']) && isset($params['maxLat']) && isset($params['maxLng']))
		{
			$maxResults = 10;
			if(isset($params['maxResults']))
			{
				$maxResults = $params['maxResults'];
			}
			$sql = "SELECT * FROM trail WHERE lat > :minLat AND lat < :maxLat AND lng > :minLng AND lng < :maxLng";
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array(':minLat'=>$params['minLat'],':maxLat'=>$params['maxLat'],':minLng'=>$params['minLng'],':maxLng'=>$params['maxLng']));
			$result = $stmt->fetchAll();
			
			if(!empty($result))
			{
				$responseObj['args'] = array("success"=>"Trails retrieved successfully.");
				$responseObj['result'] = $result;	
			}
			else
			{
				$responseObj['args'] = array("error"=>"No trails in the given area exist.");
			}
		}
		else
		{
			$responseObj['args'] = array("error"=>"Invalid parameters");
		}
    }
	else
	{
		$responseObj['args'] = array("error"=>"Invalid API key");
	}

	$this->logger->addInfo("[" . $request->getAttribute('ip_address') . "] GetTrailInArea endpoint request - Parameters:" . json_encode($params) . ", Response: " . json_encode($responseObj['args']));
	$response->getBody()->write(json_encode($responseObj));
    return $response;
});

$app->get('/PostComment/', function (Request $request, Response $response)
{
    $params = $request->getQueryParams();
	$responseObj = array();
	
	if(isKeyValid($this->db,$params['key']))
    {
		$namedParameters = array(":key"=>$params['key']);
		$sql = "SELECT id FROM user WHERE api_key=:key LIMIT 1";
		$stmt = $this->db->prepare($sql);
		$stmt->execute($namedParameters);
		$result = $stmt->fetch();
		$lat = 0.0;
		$lng = 0.0;
		
		if(isset($params['lat']))
			$lat = $params['lat'];
		
		if(isset($params['lng']))
			$lat = $params['lng'];
		
		if(isset($params['trailId']) && isset($params['commentBody']))
		{
			
			$namedParameters = array(":userId"=>$result['id'],":trailId"=>$params['trailId'],":body"=>$params['commentBody'],":lat"=>0,":lng"=>0);
			$sql = "INSERT INTO comment (userId,trailId,body,lat,lng) VALUES(:userId,:trailId,:body,:lat,:lng)";
			$stmt = $this->db->prepare($sql);
			$stmt->execute($namedParameters);
			
			$responseObj['args'] = array("success"=>"Comment added successfully.");
		}
		else
		{
			$responseObj['args'] = array("error"=>"Invalid parameters.");
		}
		
	}
	else
	{
		$responseObj['args'] = array("error"=>"Invalid API key.");
	}
	
	$this->logger->addInfo("[" . $request->getAttribute('ip_address') . "] PostComment endpoint request - Parameters:" . json_encode($params) . ", Response: " . json_encode($responseObj['args']));
	$response->getBody()->write(json_encode($responseObj));
    return $response;
	
	
});

$app->get('/RetrieveCommentsByTrailId/', function (Request $request, Response $response)
{
    $params = $request->getQueryParams();
	$responseObj = array();
	
	if(isKeyValid($this->db,$params['key']))
    {
		
		if(isset($params['trailId']))
		{
			$sql = "SELECT body, username, lat, lng FROM comment LEFT JOIN user on comment.userId = user.id WHERE trailId=:trailId";
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array(":trailId"=>$params["trailId"]));
			$result = $stmt->fetchAll();
			$responseObj['args'] = array("success"=>"Comments retrieved successfully.");
			$responseObj['result'] = $result;
		}
		else
		{
			$responseObj['args'] = array("error"=>"Invalid parameters.");
		}
		
	}
	else
	{
		$responseObj['args'] = array("error"=>"Invalid API key.");
	}
	
	$this->logger->addInfo("[" . $request->getAttribute('ip_address') . "] RetrieveCommentsByTrailId endpoint request - Parameters:" . json_encode($params) . ", Response: " . json_encode($responseObj['args']));
	$response->getBody()->write(json_encode($responseObj));
    return $response;
});

$app->get('/RetrieveCommentsByTrailName/', function (Request $request, Response $response)
{
    $params = $request->getQueryParams();
	$responseObj = array();
	
	if(isKeyValid($this->db,$params['key']))
    {
		
		if(isset($params['trailName']))
		{
			$sql = "SELECT body, username, comment.lat, comment.lng FROM trail INNER JOIN comment ON trail.id = comment.trailId  LEFT JOIN user ON comment.userId=user.id WHERE trailName=:trailName";
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array(":trailName"=>$params["trailName"]));
			$result = $stmt->fetchAll();
			$responseObj['args'] = array("success"=>"Comments retrieved successfully.");
			$responseObj['result'] = $result;
		}
		else
		{
			$responseObj['args'] = array("error"=>"Invalid parameters.");
		}
		
	}
	else
	{
		$responseObj['args'] = array("error"=>"Invalid API key.");
	}
	
	$this->logger->addInfo("[" . $request->getAttribute('ip_address') . "] RetrieveCommentsByTrailName endpoint request - Parameters:" . json_encode($params) . ", Response: " . json_encode($responseObj['args']));
	$response->getBody()->write(json_encode($responseObj));
    return $response;
});



$app->run();


?>
