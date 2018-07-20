<?php

use Core\User\API\Route;
use App\Controller as Ctrl;

// General routes
Route::get('/', [Ctrl\PostMeta::class, 'index']);
Route::get('form', [Ctrl\PostMeta::class, 'form']);
Route::post('upload', [Ctrl\PostMeta::class, 'upload']);
Route::post('/', function ($request, $response) {
    echo '<pre>';
    var_dump($request);
    echo '</pre>';
});

// User routes
Route::get('login', [Ctrl\User::class, 'loginPage']);
Route::post('login', [Ctrl\User::class, 'loginProcess']);
Route::post('logout', [Ctrl\User::class, 'logoutProcess']);
Route::get('/u/{username}', [Ctrl\User::class, 'user']);
Route::get('admin', [Ctrl\User::class, 'adminPage'])->middleware(function ($request, $response, $args) {
    if (empty($_COOKIE['id'])) {
        $response->redirect('login');
    }

    return true;
});