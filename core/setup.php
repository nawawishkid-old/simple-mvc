<?php

use Core\Config;
use Core\Router;
use Core\View;
use Core\Request;
use Core\Response;

$config = new Config();
$config->loadModule('view');
// var_dump($config->get('view', 'hahahhaha'));

// echo '<pre>';
// var_dump($_SERVER);
// var_dump($_REQUEST);
// echo '</pre>';

$request = new Request();
$response = new Response();

$router = new Router($request, $response);
$router->get('/', function ($request, $response) {
    $response->data('<h1>Hi!</h1>');
    $response->emit();
});
$router->get('hello', function ($request, $response) {
    $response->data('<h1>HELLOO!</h1>');
    $response->emit();
});
$router->get('user/{user_id}/article/{article_id}', function ($request, $response, $args) {
    // var_dump($args);
    $response->data('<h1>Hello, user no. ' . $args['user_id'] . '. With article no. ' . $args['article_id'] . '</h1>');
    $response->emit();
});

$router->notFound(function ($request, $response) {
    $response->status(404);
    $response->data('<h1>404 Not found!</h1>');
    $response->emit();
});
$router->activate();

// $view = new View($config->get('view'));
// $response->data(
//     $view->get('home', ['name' => 'Nawawish'])
// );
// // $response->header('nawawish', 'hahahaha');
// $response->emit();