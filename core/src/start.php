<?php

use Core\Config;
use Core\API\Router;
use Core\Output\View;
use Core\Input\Request;
use Core\Output\Response;
use Core\Support\Collection;

Config::init();
Config::loadModule('view');

// echo '<pre>';
// var_dump($_SERVER);
// var_dump($_REQUEST);
// echo '</pre>';

$request = new Request();
$response = new Response();

$router = new Router($request, $response);

// $collection = new Collection([10, 20, 30]);
// $collection->map(function ($item) {
//     return $item * 2;
// });
// var_dump($collection->last());