<?php

use Core\Config;
use Core\Router;
use Core\View;
use Core\Request;
use Core\Response;
use Core\Model;

Config::init();
Config::loadModule('view');

// echo '<pre>';
// var_dump($_SERVER);
// var_dump($_REQUEST);
// echo '</pre>';

$request = new Request();
$response = new Response();

$router = new Router($request, $response);

$model = new Model();
// $model->db->select(['column_a', 'column_b'], 'table_a')
//             ->where('column_a', '<', 555)
//             ->where('column_a', '<>', 333)
//             ->orWhere('column_b', '=', 'hahaha');
$model->db->select(['*'], 'wp_posts')
            ->where('ID', '>', 3);
var_dump($model->db->get());