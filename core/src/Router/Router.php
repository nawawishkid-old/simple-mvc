<?php

namespace Core\Router;

use Core\Http\Request;
use Core\Http\Response;
use Core\Controller;
use Core\Router\Middleware;

class Router
{
    private $request;

    private $response;

    private $registeredRoutes = [];

    private $previousRegisteredMethod;

    private $notFoundCallback;

    private $matchedRoute = [
        'route' => null,
        'method' => null,
        'arguments' => [],
        'callback' => null
    ];

    private $validMethods = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'PATCH',
        'OPTIONS'
    ];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @api
     */
    public function resolve()
    {
        $request = $this->request;
        $uri = $this->addPrefixSlash($request->uri);

        if (!$this->methodIsValid($request->method)) {
            throw new \Exception("Invalid request method: $request->method", 1);
            
        }

        $this->findMatchedRoute();

        if (empty($this->matchedRoute['route'])) {
            $this->executeCallback($this->notFoundCallback);
        }

        $modifiedUri = $this->modifyRoute($uri);

        if (! empty($this->getMiddlewares($request->method, $modifiedUri))) {
            $middleware = $this->executeMiddlewares($request->method, $modifiedUri);

            if (! $middleware) {
                return;
            }
        }
        
        return $this->executeCallback();
    }

    private function executeMiddlewares($method, $route)
    {
        foreach ($this->getMiddlewares($method, $route) as $key => $middleware) {
            if (is_array($middleware) && is_a($middleware, Middelware::class)) {
                $middleware = new $middleware;

                $result = $this->executeCallback([$middleware, 'run']);
            } else {
                $result = $this->executeCallback($middleware);
            }

            if (! $result) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get array of middlewares of given route
     * 
     * @param string $method HTTP request method for the middleware
     * @param string $route Modified URI of the middleware
     * 
     * @return array
     */
    private function getMiddlewares($method, $route)
    {
        $route = empty($this->registeredRoutes[$method][$route]) ? null : $this->registeredRoutes[$method][$route];

        if (empty($route)) {
            return null;
        }

        return empty($route['middlewares']) ? null : $route['middlewares'];
    }

    /**
     * Add middleware to the previous registered route
     * 
     * @api
     * @param callable|closure|string $middleware Name of middleware or function to apply.
     */
    public function middleware($middleware)
    {
        $this->throwTypeIsValid($middleware, [
            'callable',
            'array',
            'string'
        ]);

        $routeKeys = array_keys($this->registeredRoutes[$this->previousRegisteredMethod]);
        $prevRouteKey = end($routeKeys);
        $prevRoute = end($this->registeredRoutes[$this->previousRegisteredMethod]);

        if (empty($prevRoute['middlewares'])) {
            $prevRoute['middlewares'] = [];
        }

        $prevRoute['middlewares'][] = $middleware;

        $this->registeredRoutes[$this->previousRegisteredMethod][$prevRouteKey] = $prevRoute;

        return $this;
    }

    /**
     * @api
     */
    public function get(string $route, $callback)
    {
        $this->registerRoute('GET', $route, $callback);

        return $this;
    }

    /**
     * @api
     */
    public function post(string $route, $callback)
    {
        $this->registerRoute('POST', $route, $callback);

        return $this;
    }

    /**
     * @api
     */
    public function put(string $route, $callback)
    {
        $this->registerRoute('PUT', $route, $callback);

        return $this;
    }

    /**
     * @api
     */
    public function delete(string $route, $callback)
    {
        $this->registerRoute('DELETE', $route, $callback);

        return $this;
    }

    /**
     * @api
     */
    public function patch(string $route, $callback)
    {
        $this->registerRoute('PATCH', $route, $callback);

        return $this;
    }

    /**
     * @api
     */
    public function options(string $route, $callback)
    {
        $this->registerRoute('OPTIONS', $route, $callback);

        return $this;
    }

    /**
     * @api
     */
    public function notFound($callback)
    {
        $this->notFoundCallback = $callback;

        return $this;
    }

    private function registerRoute(string $method, string $route, $callback)
    {
        $this->throwTypeIsValid($callback, [
            'callable',
            'array',
            'string'
        ]);

        if (!$this->methodIsValid($method)) {
            throw new \Exception("Invalid request method, $method", 1);
            
        }

        $this->previousRegisteredMethod = $method;

        if (empty($this->registeredRoutes[$method])) {
            $this->registeredRoutes[$method] = [];
        }

        $routeInfo = [
            'originalRoute' => $route,
            'callback' => $callback,
            // 'middlewares' => []
        ];
        
        $this->registeredRoutes[$method][$this->modifyRoute($route)] = $routeInfo;
    }

    private function executeCallback($callback = null)
    {
        if (is_null($callback)) {
            $callback = $this->matchedRoute['callback'];
            $extraArguments = (object) $this->matchedRoute['arguments'];
        } else {
            $extraArguments = null;
        }

        if (is_null($callback)) {
            return;
        }

        $this->throwTypeIsValid($callback, [
            'callable',
            'array',
            'string'
        ]);

        // Instantiate class if necessary.
        if (is_array($callback) && is_a($callback[0], Controller::class)) {
            $callback[0] = new $callback[0];
        }

        return call_user_func_array($callback, [$this->request, $this->response, $extraArguments]);
    }

    private function findMatchedRoute()
    {
        $method = $this->request->method;
        $uri = $this->request->uri;

        // If no route registered
        if (empty($this->registeredRoutes[$method])) {
            return;
        }

        foreach ($this->registeredRoutes[$method] as $route => $value) {
            $matches = $this->matchRouteWithURI($route, $uri);

            if (! empty($matches)) {
                unset($matches[0]);
                $this->addMatchedRouteInfo($route, $method, array_values($matches));
                
                return;
            }
        }
    }

    private function addMatchedRouteInfo(string $routeName, string $method, array $arguments)
    {
        $this->matchedRoute['route'] = $routeName;
        $this->matchedRoute['method'] = $method;
        $this->matchedRoute['callback'] = $this->registeredRoutes[$method][$routeName]['callback'];

        $routeParameters = $this->findRouteParameters($this->registeredRoutes[$method][$routeName]['originalRoute']);

        foreach ($arguments as $index => $argument) {
            $this->matchedRoute['arguments'][$routeParameters[$index]] = $argument;
        }
    }

    private function matchRouteWithURI(string $route, string $uri)
    {
        $pattern = '@^' . $route . '/?$@';
        preg_match($pattern, $uri, $matches);

        return $matches;
    }

    private function findRouteParameters(string $route)
    {
        preg_match_all('@{(.*?)}@', $route, $matches);

        unset($matches[0]);

        $results = $this->unnestArrayRecursively(array_values($matches));

        return $results;
    }

    private function unnestArrayRecursively(array $array)
    {
        $newArray = [];

        foreach ($array as $key => $item) {
            if (gettype($item) === 'array') {
                $newArray = array_merge($newArray, $this->unnestArrayRecursively($item));
                continue;
            }

            $newArray[] = $item;
        }

        return $newArray;
    }

    private function modifyRoute(string $route)
    {
        return $this->convertRouteToRegexPattern($this->addPrefixSlash($route));
    }

    private function addPrefixSlash(string $string)
    {
        if (mb_substr($string, 0, 1) !== '/') {
            $string = '/' . $string;
        }

        return $string;
    }

    private function convertRouteToRegexPattern(string $route, bool $required = true)
    {
        $modifier = $required ? '+' : '*';

        $result = preg_replace('/{.*?}/', '(\w' . $modifier . ')', $route);

        return $result;
    }

    private function throwTypeIsValid($var, array $types)
    {
        if (! $this->typeIsValid($var, $types)) {
            throw new \Exception("Error: Invalid argument types; Valid type(s) is/are " . implode(', ', $types) . "; " . gettype($var) . " given", 1);
            
        }

        return true;
    }

    private function typeIsValid($var, array $types)
    {
        foreach ($types as $type) {
            if (! function_exists('is_' . $type)) {
                throw new \Exception("Error: Unknown type, $type", 1);
                
            }

            $result = call_user_func_array('is_' . $type, [$var]);

            if ($result) {
                return true;
            }
        }

        return false;
    }

    private function methodIsValid(string $method)
    {
        return in_array($method, $this->validMethods);
    }
}