<?php

namespace Core;

// use Core\Http\Request;
// use Core\Router\Router;
use Core\User\API\Route as UserRouteApi;
// use Core\User\API\Model as UserModelApi;
use Core\Database\Model;
// use App\Model as AppModel;

class App
{
    private $request;

    private $response;

    private $components = [];

    // Just store Model::class - -"
    private $services = [];

    const REQUEST_KEY = 'request';
    const RESPONSE_KEY = 'response';
    const COMPONENTS_ROUTER_KEY = 'router';
    const COMPONENTS_DATABASE_CONNECTION_KEY = 'databaseConnection';
    const COMPONENTS_DATABASE_CONTROLLER_KEY = 'databaseController';

    const MODEL_DIRECTORY = APP_DIR . '/Model';
    const CONTROLLER_DIRECTORY = APP_DIR . '/Controller';

    /**
     * @api
     */
    public function __construct(array $classes)
    {
        $this->request = new $classes[self::REQUEST_KEY];
        $this->response = new $classes[self::RESPONSE_KEY];

        // Isn't it should be named 'services' instead of 'components'?
        $this->components['router'] = new $classes[self::COMPONENTS_ROUTER_KEY]($this->request, $this->response);
        $this->components['databaseConnection'] = new $classes[self::COMPONENTS_DATABASE_CONNECTION_KEY];
        $this->components['databaseController'] = new $classes[self::COMPONENTS_DATABASE_CONTROLLER_KEY]($this->components['databaseConnection']);

        // Still don't know what to do with $this->services
        $this->services['Model'] = $classes['model'];

        $this->initial();
    }

    /**
     * @api
     */
    public function __destruct()
    {
        UserRouteApi::resolve();
        unset($this->components['databaseController']);
    }

    private function initial()
    {
        // UserModelApi::initial();
        UserRouteApi::initial($this->components['router']);

        $this->loadModels();
        $this->loadControllers();
    }

    /**
     * @api
     */
    // public function end()
    // {
    //     UserRouteApi::resolves();
    // }

    private function loadModels()
    {
        $this->includeDirectory(self::MODEL_DIRECTORY);
        // $this->initializeModels();
    }

    private function loadControllers()
    {
        $this->includeDirectory(self::CONTROLLER_DIRECTORY);
    }

    private function initializeModels()
    {
        $this->iteratesFiles(self::MODEL_DIRECTORY, function ($filepath) {
            $filename = pathinfo($filepath, PATHINFO_FILENAME);
            // $className = '\\App\\Model\\' . $filename;
            $className = '\\App\\Model\\' . $filename;

            $this->models[] = $className;

            var_dump($this->models);

            // Find a way to use model like eloquent
            $model = new $this->services['Model']($className::$table, $this->components['databaseController']);

            \call_user_func_array([$className, 'initial'], [$model]);
        });
    }

    private function includeDirectory(string $directoryPath)
    {
        // var_dump($directoryPath);
        $this->iteratesFiles($directoryPath, function ($filepath) {
            // var_dump($filename);
            // echo '<br>';
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