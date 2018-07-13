<?php

use Core\App;
use Core\Http\Request;
use Core\Http\Response;
use Core\Router\Router;
use Core\Database\Controller as DatabaseController;
use Core\Database\Connection as DatabaseConnection;
use Core\Database\Model;

$app = new App([
    'request' => Request::class,
    'response' => Response::class,
    'router' => Router::class,
    'databaseConnection' => DatabaseConnection::class,
    'databaseController' => DatabaseController::class,
    'model' => Model::class
]);