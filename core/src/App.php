<?php

namespace Core\App;

// use Core\Http\Request;
// use Core\Router\Router;
use User\API\Route;

class App
{
    private $request;

    private $response;

    private $router;

    public function __construct(array $classes)
    {
        $this->request = new $classes['request'];
        $this->response = new $classes['response'];
        $this->router = new $classes['router']($this->request, $this->response);
    }

    private function start()
    {
        Route::wait($this->router);
    }

    private function end()
    {
        Route::ready();
    }
}