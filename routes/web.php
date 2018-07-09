<?php


$router->get('/', function ($request, $response) {
    $response->data('<h1>Hi!</h1>');
    $response->emit();
});
$router->get('hello', function ($request, $response) {
    $response->data('<h1>HELLOO!</h1>');
    $response->emit();
});
$router->get('user/{user_id}/article/{article_id}', function ($request, $response, $args) {
    $response->data('<h1>Hello, user no. ' . $args->user_id . '. With article no. ' . $args->article_id . '</h1>');
    $response->emit();
});
$router->notFound(function ($request, $response) {
    $response->status(404);
    $response->data('<h1>404 Not found!</h1>');
    $response->emit();
});