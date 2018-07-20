<?php

use Core\App;
use Core\Http\Request;
use Core\Http\Response;
use Core\Router\Router;
use Core\View\View;
use Core\Database\Controller as DatabaseController;
use Core\Database\Connection as DatabaseConnection;
use Core\Database\Model;

echo '<pre>';
// var_dump($_SERVER);
// var_dump($_REQUEST);
// var_dump($_FILES);
echo '</pre>';

$request = new Request();
$response = new Response();

$request->header = getallheaders();
$request->method = $_SERVER['REQUEST_METHOD'];
$request->uri = $_SERVER['REQUEST_URI'];
$request->get = $_GET;
$request->post = $_POST;
$request->files = $_FILES;
$request->sessions = empty($_SESSION) ? [] : $_SESSION;
$request->cookie = $_COOKIE;

$app = new App($request, $response, [
    'router' => Router::class,
    'view' => View::class,
    'databaseConnection' => DatabaseConnection::class,
    'databaseController' => DatabaseController::class,
    'model' => Model::class
]);