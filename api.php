<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require 'db_params.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

//create connection for database
$config['db']['host']   = $host;
$config['db']['user']   = $user;
$config['db']['pass']   = $pass;
$config['db']['dbname'] = $dbName;

//start a new slim application
$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

//setup the PDO
$container['db'] = function ($c) 
{
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};


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
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array(':fbid'=>$fields['fbid']));
		$result = $stmt->fetch();
		
		if($result['count'] > 0)
		{
			$args["fbid"] = "FacebookID is already registered.";
		}
	}
	
	if(sizeof($args) > 0)
	{
		$responseObj['args'] = $args;
		$response->getBody()->write(json_encode($responseObj));
		return true;
	}
	
	return false;
}

$app->get('/GetTrailById/{id}', function (Request $request, Response $response)
{
    $params = $request->getQueryParams();

	if(isKeyValid($this->db,$params['key']))
	{
       $id = $request->getAttribute('id');
       $sql = "SELECT * FROM trail WHERE id=:id LIMIT 1";
       $stmt = $this->db->prepare($sql);
       $stmt->execute(array(':id'=>$id));
       $result = $stmt->fetch();
       $response->getBody()->write($result['trailObj']);
       return $response;
	}
    $response->getBody()->write("Invalid API key");
    return $response;

});

$app->get('/WriteTrailToDB/', function (Request $request, Response $response)
{
    $params = $request->getQueryParams();
    if(isKeyValid($this->db,$params['key']))
    {
		if(isset($params['lat']) && isset($params['lng']) && isset($params['trailObj']))
		{
			$sql = "INSERT INTO trail (trailInfo,lat,lng,trailObj) VALUES('Empty description',:lat,:lng,:trailObj)";
			$stmt = $this->db->prepare($sql);
			$stmt->execute(array(':lat'=>$params['lat'],':lng'=>$params['lng'],':trailObj'=>$params['trailObj']));
			$response->getBody()->write("Trail added successfully" . " " . $params['lat'] . " " . $params['lng'] . " " . $params['trailObj']);
		}
		else
		{
			$response->getBody()->write("Invalid parameters");
		}
    }
	else
	{
		$response->getBody()->write("Invalid API key");	
	}
    
    return $response;
});

$app->get('/GetTrailInArea/', function (Request $request, Response $response)
{
    $params = $request->getQueryParams();
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
			$response->getBody()->write(json_encode($result));
		}
		else
		{
			$response->getBody()->write("Invalid parameters");
		}
    }
	else
	{
		$response->getBody()->write("Invalid API key");	
	}
	
    return $response;
});

$app->get('/RegisterUser/', function (Request $request, Response $response)
{
	$params = $request->getQueryParams();
	$fields = array("username"=>$params['username'],"email"=>$params['email']);
	if(!accountExists($this->db,$response,$fields))
	{
		$sql = "INSERT INTO user (username, password, email, api_key) VALUES(:username, :password, :email, :api_key)";
		$stmt = $this->db->prepare($sql);
		$stmt->execute(array(':username'=>$params['username'], ':password'=>$params['password'], ':email'=>$params['email'], ':api_key'=>md5($params['email'])));
		$responseObj = array("args"=>array("success"=>"Registered Successfully"));
		$response->getBody()->write(json_encode($responseObj));
	}
	return $response;
});

$app->get('/Login/', function(Request $request, Response $response)
{
	$params = $request->getQueryParams();

	$sql = "SELECT api_key FROM user WHERE username=:username AND password=:password LIMIT 1";
	$stmt = $this->db->prepare($sql);
	$stmt->execute(array(':username'=>$params['username'],':password'=>$params['password']));
	$result = $stmt->fetch();

	if(!isset($result['api_key']))
	{
		$response->getBody()->write("Invalid login credentials");
	}
	else
	{
		$response->getBody()->write($result['api_key']);
	}

	return $response;

});

$app->get('/RegisterUserWithFB/', function (Request $request, Response $response)
{
	$params = $request->getQueryParams();

	$sql = "SELECT COUNT(*) count FROM user WHERE facebookId=:fbid LIMIT 1";
	$stmt = $this->db->prepare($sql);
    $stmt->execute(array(':fbid'=>$params['fbid']));
    $result = $stmt->fetch();

    if($result['count'] > 0)
	{
		$response->getBody()->write("Facebook ID already in use.");
		return $response;
	}

	$sql = "INSERT INTO user (username, email, facebookId, api_key) VALUES(:username, :email, :fbid, :api_key)";
	$stmt = $this->db->prepare($sql);
	$stmt->execute(array(':username'=>$params['username'],':email'=>$params['email'], ':fbid'=>$params['fbid'], ':api_key'=>md5($params['email'])));
	$response->getBody()->write("Registered Successfully");
	return $response;
});

$app->get('/LoginWithFB/', function(Request $request, Response $response)
{
	$params = $request->getQueryParams();

	$sql = "SELECT api_key FROM user WHERE facebookId = :fbid LIMIT 1";
	$stmt = $this->db->prepare($sql);
	$stmt->execute(array(':fbid'=>$params['fbid']));
	$result = $stmt->fetch();

	if(!isset($result['api_key']))
	{
		$response->getBody()->write("Invalid login credentials");
	}
	else
	{
		$response->getBody()->write($result['api_key']);
	}

	return $response;

});

$app->run();


?>
