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

$app->get('/GetTrailById/{id}', function (Request $request, Response $response) {
    $id = $request->getAttribute('id');
    $sql = "SELECT * FROM trail WHERE id=:id LIMIT 1";
    $stmt = $this->db->prepare($sql);
    $stmt->execute(array(':id'=>$id));
    $result = $stmt->fetch();
    $response->getBody()->write($result['trailInfo']);
    return $response;
});
$app->run();
?>
