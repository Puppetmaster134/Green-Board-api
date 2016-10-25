<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require '../vendor/autoload.php';
require 'db_params.php';

$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$config['db']['host']   = $host;
$config['db']['user']   = $user;
$config['db']['pass']   = $pass;
$config['db']['dbname'] = $dbName;


$app = new \Slim\App(["settings" => $config]);
$container = $app->getContainer();

$container['db'] = function ($c) {
    $db = $c['settings']['db'];
    $pdo = new PDO("mysql:host=" . $db['host'] . ";dbname=" . $db['dbname'],
        $db['user'], $db['pass']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    return $pdo;
};

$app->get('/GetTrailById/{id}', function (Request $request, Response $response) 
{
    $params = $request->getQueryParams();
    $sql = "SELECT COUNT(*) count FROM user WHERE api_key=:key LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(array(':key'=>$params['key']));
    $result = $stmt->fetch();

    if($result['count'] > 0)
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
    $sql = "SELECT COUNT(*) count FROM user WHERE api_key=:key LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(array(':key'=>$params['key']));
    $result = $stmt->fetch();

    if($result['count'] > 0 && isset($params['lat']) && isset($params['lng']) && isset($params['trailObj']))
    {
       $sql = "INSERT INTO trail (trailInfo,lat,lng,trailObj) VALUES('Empty description',:lat,:lng,:trailObj)";
       $stmt = $this->db->prepare($sql);
       $stmt->execute(array(':lat'=>$params['lat'],':lng'=>$params['lng'],':trailObj'=>$params['trailObj']));
       $response->getBody()->write("Trail added successfully" . " " . $params['lat'] . " " . $params['lng'] . " " . $params['trailObj']);
       return $response;
    }
    $response->getBody()->write("Invalid API key");
    return $response;
});

$app->get('/GetTrailInArea/', function (Request $request, Response $response)
{
    $params = $request->getQueryParams();
    $sql = "SELECT COUNT(*) count FROM user WHERE api_key=:key LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(array(':key'=>$params['key']));
    $result = $stmt->fetch();

    if($result['count'] > 0 && isset($params['minLat']) && isset($params['minLng']) && isset($params['maxLat']) && isset($params['maxLng']))
    {
	
	   $maxResults = 10;
	   if(isset($params['maxResults']))
	   {
		   $maxResults = $params['maxResults'];
	   }
	   
	   
	   /*
       $sql = "SELECT * FROM trail WHERE (lat > :minLat) AND (lat < :maxLat) AND (lng > :minLng) AND (lng < :maxLng)  LIMIT :maxResults";
       $stmt = $this->db->prepare($sql);
       $stmt->execute(array(':minLat'=>$params['minLat'],':maxLat'=>$params['maxLat'],':minLng'=>$params['minLng'],':maxLng'=>$params['maxLng'],':maxResults'=>$maxResults));
       */
	   $response->getBody()->write("Endpoint is not developed yet");
       return $response;
    }
    $response->getBody()->write("Invalid API key");
    return $response;
});
$app->run();
?>
