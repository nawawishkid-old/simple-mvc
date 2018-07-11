<?php

namespace Core\API\Route;

use Core\API\Route\Router;
// use Core\API\Route\Middleware;

class Route
{
    private static $router;

    private static $middleware;

    public static function start(Router $router)
    {
        self::$router = $router;
        // self::$middleware = new Middleware();
    }

    public static function ready()
    {
        self::$router->ready();
    }

    private static function callRouter(string $method, string $route, $callback)
    {
        return \call_user_func_array([self::$router, $method], [$route, $callback]);
    }

    // public static function middleware(string $route, $callback)
    // {
    //     return self::callRouter('get', $route, $callback);
    // }

    public static function get(string $route, $callback)
    {
        return self::callRouter('get', $route, $callback);
    }

    public static function post(string $route, $callback)
    {
        return self::callRouter('post', $route, $callback);
    }

    public static function put(string $route, $callback)
    {
        return self::callRouter('put', $route, $callback);
    }

    public static function delete(string $route, $callback)
    {
        return self::callRouter('delete', $route, $callback);
    }
}