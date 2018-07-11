<?php

namespace Core\Router;

use Core\Http\Request;
use Core\Http\Response;
// use Core\Input\Request;
// use Core\Output\Response;
// use Core\Output\View;

class Router
{
    private $request;

    // private $response;

    private $registeredRoute = [];

    private $notFoundCallback;

    private $matchedRoute = [
        'route' => null,
        'method' => null,
        'arguments' => [],
        'callback' => null
    ];

    private $validMethod = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'OPTION'
    ];

    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * @api
     */
    public function ready()
    {
        $request = $this->request;
        $uri = $this->addPrefixSlash($request->uri);

        if (!$this->methodIsValid($request->method)) {
            throw new \Exception("Invalid request method: $request->method", 1);
            
        }

        $this->findMatchedRoute();

        if (empty($this->matchedRoute['route'])) {
            $this->executeCallback($this->notFoundCallback);
            
            return;
        }

        $this->executeCallback();
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

        if (empty($this->registeredRoute[$method])) {
            $this->registeredRoute[$method] = [];
        }

        $routeInfo = [
            'originalRoute' => $route,
            'callback' => $callback
        ];
        
        $this->registeredRoute[$method][$this->modifyRoute($route)] = $routeInfo;
    }

    private function executeCallback($callback = null)
    {
        if (\is_null($callback)) {
            $callback = $this->matchedRoute['callback'];
            $extraArguments = (object) $this->matchedRoute['arguments'];
        } else {
            $extraArguments = null;
        }

        if (\is_null($callback)) {
            return;
        }

        $this->throwTypeIsValid($callback, [
            'callable',
            'array',
            'string'
        ]);

        // Instantiate class if necessary.
        if (\is_array($callback) && \is_a($callback[0], Controller::class)) {
            $callback[0] = new $callback[0];
        }

        \call_user_func_array($callback, [$this->request, $this->response, $extraArguments]);
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
            if (! \function_exists('is_' . $type)) {
                throw new \Exception("Error: Unknown type, $type", 1);
                
            }

            $result = \call_user_func_array('is_' . $type, [$var]);

            if ($result) {
                return true;
            }
        }

        return false;
    }

    private function methodIsValid(string $method)
    {
        return \in_array($method, $this->validMethod);
    }

    private function findMatchedRoute()
    {
        $method = $this->request->method;
        $uri = $this->request->uri;

        // If no route registered
        if (empty($this->registeredRoute[$method])) {
            return;
        }

        foreach ($this->registeredRoute[$method] as $route => $value) {
            $matches = $this->matchRouteWithURI($route, $uri);

            if (! empty($matches)) {
                unset($matches[0]);
                $this->addMatchedRouteInfo($route, $method, \array_values($matches));
                
                return;
            }
        }
    }

    private function addMatchedRouteInfo(string $routeName, string $method, array $arguments)
    {
        $this->matchedRoute['route'] = $routeName;
        $this->matchedRoute['method'] = $method;
        $this->matchedRoute['callback'] = $this->registeredRoute[$method][$routeName]['callback'];

        $routeParameters = $this->findRouteParameters($this->registeredRoute[$method][$routeName]['originalRoute']);

        foreach ($arguments as $index => $argument) {
            $this->matchedRoute['arguments'][$routeParameters[$index]] = $argument;
        }
    }

    private function matchRouteWithURI(string $route, string $uri)
    {
        $pattern = '@^' . $route . '/?$@';
        \preg_match($pattern, $uri, $matches);

        return $matches;
    }

    private function findRouteParameters(string $route)
    {
        \preg_match_all('@{(.*?)}@', $route, $matches);

        unset($matches[0]);

        $results = $this->unnestArrayRecursively(\array_values($matches));

        return $results;
    }

    private function unnestArrayRecursively(array $array)
    {
        $newArray = [];

        foreach ($array as $key => $item) {
            if (\gettype($item) === 'array') {
                $newArray = \array_merge($newArray, $this->unnestArrayRecursively($item));
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
        if (\mb_substr($string, 0, 1) !== '/') {
            $string = '/' . $string;
        }

        return $string;
    }

    private function convertRouteToRegexPattern(string $route, bool $required = true)
    {
        $modifier = $required ? '+' : '*';

        $result = \preg_replace('/{.*?}/', '(\w' . $modifier . ')', $route);

        return $result;
    }
}