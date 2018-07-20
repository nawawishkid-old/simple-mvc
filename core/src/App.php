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
     * Start the application.
     * 
     * @uses App::instantiateServices()
     * @uses App::initial()
     * 
     * @param Request $request HTTP Request instance.
     * @param Response $request HTTP Response instance.
     * @param array $services Array of services use in the application.
     * 
     * @return void
     */
    public function __construct(Request $request, Response $response, array $services)
    {
        $this->request = $request;
        $this->response = $response;

        $this->instantiateServices($services);
        // $this->services[static::MODEL_KEY] = $services[static::MODEL_KEY];

        $this->initial();
    }

    /**
     * Stop the application.
     * 
     * @uses App::respond()
     * 
     * @return void
     */
    public function __destruct()
    {
        $this->respond();

        unset($this->services[static::DATABASE_CONTROLLER_KEY]);
    }

    /**
     * Instantiate all application related services class.
     * 
     * @param array $services Array of services use in the application.
     */
    private function instantiateServices(array $services)
    {
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
    }

    /**
     * Initialize the app.
     * 
     * @uses App::loadModels()
     * @uses App::loadControllers()
     * @uses \Core\User\API\Route::initial()
     * 
     * @return void
     */
    private function initial()
    {
        // UserModelApi::initial();
        UserRouteApi::initial($this->services[static::ROUTER_KEY]);

        $this->loadModels();
        $this->loadControllers();
    }

    /**
     * Respond back to the client via Response instance.
     * 
     * @uses \Core\User\API\Route::resolve()
     * 
     * @return void
     */
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

    /**
     * Load user-defined model from the model directory.
     * 
     * @uses App::includeDirectory()
     * 
     * @return void
     */
    private function loadModels()
    {
        $this->includeDirectory(static::MODEL_DIRECTORY);
        // $this->initializeModels();
    }

    /**
     * Load user-defined controllers from the controller directory.
     * 
     * @uses App::includeDirectory()
     * 
     * @return void
     */
    private function loadControllers()
    {
        $this->includeDirectory(static::CONTROLLER_DIRECTORY);
    }

    // private function initializeModels()
    // {
    //     $this->iteratesFiles(static::MODEL_DIRECTORY, function ($filepath) {
    //         $filename = pathinfo($filepath, PATHINFO_FILENAME);
    //         // $className = '\\App\\Model\\' . $filename;
    //         $className = '\\App\\Model\\' . $filename;

    //         $this->models[] = $className;

    //         // var_dump($this->models);

    //         // Find a way to use model like eloquent
    //         $model = new $this->services[static::MODEL_KEY]($className::$table, $this->services[static::DATABASE_CONTROLLER_KEY]);

    //         \call_user_func_array([$className, 'initial'], [$model]);
    //     });
    // }

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