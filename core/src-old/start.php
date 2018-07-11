<?php

use Core\Config;
use Core\API\Route\Route;
use Core\API\Route\Router;
// use Core\API\Route\Middleware;
use Core\Output\View;
use Core\Input\Request;
use Core\Output\Response;
use Core\Support\Collection;

Config::init();
// Config::loadModule('view');

// echo '<pre>';
// var_dump($_SERVER);
// var_dump($_REQUEST);
// echo '</pre>';

$request = new Request();
$response = new Response();

$router = new Router($request, $response);
// $middleware = new Middleware();

Route::start($router);

// unset($router);

// $collection = new Collection([10, 20, 30]);
// $collection->map(function ($item) {
//     return $item * 2;
// });
// var_dump($collection->last());