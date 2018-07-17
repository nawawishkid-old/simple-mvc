<?php

namespace Core;

use Core\User\API\Route as UserRouteApi;
// use Core\User\API\Model as UserModelApi;
use Core\Database\Model;
use Core\Http\Request;
use Core\Http\Response;

class App
{
    private $request;

    private $response;

    private $services = [];

    const ROUTER_KEY = 'router';
    const VIEW_KEY = 'view';
    const MODEL_KEY = 'model';
    const DATABASE_CONNECTION_KEY = 'databaseConnection';
    const DATABASE_CONTROLLER_KEY = 'databaseController';

    const MODEL_DIRECTORY = APP_DIR . '/Model';
    const CONTROLLER_DIRECTORY = APP_DIR . '/Controller';

    /**
     * @api
     */
    public function __construct(Request $request, Response $response, array $services)
    {
        $this->request = $request;
        $this->response = $response;

        // Isn't it should be named 'services' instead of 'services'?
        $this->services[static::VIEW_KEY] = new $services[static::VIEW_KEY];
        $this->services[static::ROUTER_KEY] = new $services[static::ROUTER_KEY](
            $this->request, 
            $this->response,
            $this->services[static::VIEW_KEY]
        );
        $this->services[static::DATABASE_CONNECTION_KEY] = new $services[static::DATABASE_CONNECTION_KEY];
        $this->services[static::DATABASE_CONTROLLER_KEY] = new $services[static::DATABASE_CONTROLLER_KEY](
            $this->services[static::DATABASE_CONNECTION_KEY]
        );

        // $this->services[static::MODEL_KEY] = $services[static::MODEL_KEY];

        $this->initial();
    }

    /**
     * @api
     */
    public function __destruct()
    {
        $this->respond();

        unset($this->services[static::DATABASE_CONTROLLER_KEY]);
    }

    private function initial()
    {
        // UserModelApi::initial();
        UserRouteApi::initial($this->services[static::ROUTER_KEY]);

        $this->loadModels();
        $this->loadControllers();
    }

    private function startServices()
    {
        
    }

    private function respond()
    {
        $result = UserRouteApi::resolve();
        
        if (is_a($result, get_class($this->response))) {
            $this->response = $result;
        } elseif (empty($result)) {
            $this->response->status(404, 'Not found');
        } else {
            $this->response->data($result);
        }

        $this->response->emit();
    }

    private function loadModels()
    {
        $this->includeDirectory(static::MODEL_DIRECTORY);
        // $this->initializeModels();
    }

    private function loadControllers()
    {
        $this->includeDirectory(static::CONTROLLER_DIRECTORY);
    }

    private function initializeModels()
    {
        $this->iteratesFiles(static::MODEL_DIRECTORY, function ($filepath) {
            $filename = pathinfo($filepath, PATHINFO_FILENAME);
            // $className = '\\App\\Model\\' . $filename;
            $className = '\\App\\Model\\' . $filename;

            $this->models[] = $className;

            // var_dump($this->models);

            // Find a way to use model like eloquent
            $model = new $this->services[static::MODEL_KEY]($className::$table, $this->services[static::DATABASE_CONTROLLER_KEY]);

            \call_user_func_array([$className, 'initial'], [$model]);
        });
    }

    private function includeDirectory(string $directoryPath)
    {
        $this->iteratesFiles($directoryPath, function ($filepath) {
            include_once $filepath;
        });
    }

    private function iteratesFiles(string $directoryPath, $callback)
    {
        foreach (\glob($directoryPath . '/*.php') as $filepath) {
            \call_user_func_array($callback, [$filepath]);
        }
    }
}