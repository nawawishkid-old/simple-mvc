<?php

use Core\User\API\Route;
use App\Controller as Ctrl;

Route::get('hi', function () {
    echo "Hello, world!";
});

Route::get('/u/{username}', [Ctrl\PostMeta::class, 'user']);
Route::get('/', [Ctrl\PostMeta::class, 'index'])->middleware(function () {
    echo 'MIDDLEWARE!';
})->middleware(function ($request) {
    echo '<h1>Middleware</h1>';
    echo '<pre>';
    var_dump($request);
    echo '</pre>';
});

Route::get('form', [Ctrl\PostMeta::class, 'form']);
Route::post('upload', [Ctrl\PostMeta::class, 'upload']);

Route::post('/', function ($request, $response) {
    echo '<pre>';
    var_dump($request);
    echo '</pre>';
});