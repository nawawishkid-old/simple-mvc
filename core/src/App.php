<?php

namespace Core;

// use Core\Http\Request;
// use Core\Router\Router;
use Core\User\API\Route;

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

        $this->initial();
    }

    private function initial()
    {
        Route::wait($this->router);

        $this->loadsModels();
        $this->loadsControllers();
    }

    /**
     * @api
     */
    public function end()
    {
        Route::ready();
    }

    private function loadsModels()
    {
        $this->includesDirectory(APP_DIR . '/Model');
    }

    private function loadsControllers()
    {
        $this->includesDirectory(APP_DIR . '/Controller');
    }

    private function initializesModels()
    {

    }

    private function includesDirectory(string $directoryPath)
    {
        foreach (\glob($directoryPath . '/*.php') as $filename) {
            include_once $filename;
        }
    }
}