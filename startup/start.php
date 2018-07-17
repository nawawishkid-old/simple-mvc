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

$app = new App([
    'request' => Request::class,
    'response' => Response::class,
    'router' => Router::class,
    'view' => View::class,
    'databaseConnection' => DatabaseConnection::class,
    'databaseController' => DatabaseController::class,
    'model' => Model::class
]);