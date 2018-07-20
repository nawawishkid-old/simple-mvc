<?php

namespace Core\User\API;

use Core\Router\Router;

class Route
{
    private static $router;

    private static $view;
    
    public static function __callStatic(string $methodName, $arguments)
    {
        return \call_user_func_array([static::$router, $methodName], $arguments);
    }

    public static function initial(Router $router)
    {
        static::$router = $router;
    }

    public static function resolve()
    {
        return static::$router->resolve();
    }

    // Just to make __callStatic works
    protected static function get(string $route, $callback) {}

    protected static function post(string $route, $callback) {}

    protected static function put(string $route, $callback) {}

    protected static function delete(string $route, $callback) {}

    protected static function patch(string $route, $callback) {}
}