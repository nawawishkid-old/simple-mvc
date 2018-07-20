<?php
// Will be run in public/index.php
require_once '../config.php';
require_once APP_ROOT . '/vendor/autoload.php';
require_once APP_ROOT . '/core/src/start.php';
require_once APP_ROOT . '/routes/web.php';
// require_once APP_ROOT . '/app/Model/TestModel.php';
require_once APP_ROOT . '/app/Model/Example.php';
require_once APP_ROOT . '/app/Controller/ExampleController.php';
// require_once APP_ROOT . '/routes/web.php';
require_once APP_ROOT . '/core/src/end.php';