<?php

namespace Core\User\API;

use Core\Router\Router;

class Route
{
    public static function __callStatic(string $methodName, $arguments)
    {
        return \call_user_func_array([self::$router, $methodName], $arguments);
    }

    public static function wait(Router &$router)
    {
        self::$router = $router;
    }

    public static function ready()
    {
        self::$router->ready();
    }

    // Just to make __callStatic works
    protected static function get(string $route, $callback) {}

    protected static function post(string $route, $callback) {}

    protected static function put(string $route, $callback) {}

    protected static function delete(string $route, $callback) {}

    protected static function patch(string $route, $callback) {}
}