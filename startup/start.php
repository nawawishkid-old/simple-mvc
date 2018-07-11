<?php

use Core\Http\Request;
use Core\Http\Response;
use Core\Router\Router;

$app = new App([
    'request' => Request::class,
    'response' => Response::class,
    'router' => Router::class
]);