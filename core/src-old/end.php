<?php

use Core\API\Route\Route;
use Core\Database\DBManager as DB;

Route::ready();
// $router->activate();
DB::close();