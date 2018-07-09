<?php

namespace Core;

use Core\Request;
use Core\Response;
use Core\View;

class Router
{
    private $request;

    private $response;

    private $registeredRoute = [];

    private $notFoundCallback;

    private $matchedRoute = [
        'route' => null,
        'method' => null,
        'arguments' => [],
        'callback' => null
    ];

    private $routeParameters = [];

    private $routeArguments = [];

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

    public function activate()
    {
        // echo $this->request->method;
        $request = $this->request;
        $uri = $this->addPrefixSlash($request->uri);

        if (!$this->methodIsValid($request->method)) {
            throw new \Exception("Invalid request method: $request->method", 1);
            
        }

        // echo $request;
        // var_dump($this->registeredRoute);
        // var_dump($request->method);
        // exit;

        $this->findMatchedRoute();

        if (empty($this->matchedRoute['route'])) {
            // var_dump('ERROR');
            $this->executeCallback($this->notFoundCallback);
            return;
            // return $this->response->view(\Core\View::get('404'), 404);
        }

        // echo $uri;
        // echo '<pre>';
        // var_dump($this->matchedRoute['callback']);
        // echo '</pre>';

        $this->executeCallback();
    }

    public function get(string $route, callable $callback)
    {
        $this->registerRoute('GET', $route, $callback);

        return $this;
    }

    public function post(string $route, callable $callback)
    {
        $this->registerRoute('POST', $route, $callback);

        return $this;
    }

    public function put(string $route, callable $callback)
    {
        $this->registerRoute('PUT', $route, $callback);

        return $this;
    }

    public function delete(string $route, callable $callback)
    {
        $this->registerRoute('DELETE', $route, $callback);

        return $this;
    }

    public function option(string $route, callable $callback)
    {
        $this->registerRoute('OPTION', $route, $callback);

        return $this;
    }

    public function notFound(callable $callback)
    {
        $this->notFoundCallback = $callback;

        return $this;
    }

    private function registerRoute(string $method, string $route, callable $callback)
    {
        if (!$this->methodIsValid($method)) {
            throw new \Exception("Invalid request method, $method", 1);
            
        }

        // var_dump($method);
        // var_dump($route);
        // var_dump($callback);

        if (empty($this->$registeredRoute[$method])) {
            $this->$registeredRoute[$method] = [];
        }

        $routeInfo = [
            'originalRoute' => $route,
            'callback' => $callback
        ];
        
        $this->registeredRoute[$method][$this->modifyRoute($route)] = $routeInfo;
        
        // echo '<pre>';
        // var_dump($this->registeredRoute);
        // echo '</pre>';
    }

    private function executeCallback(callable $callback = null)
    {
        if (is_null($callback)) {
            $callback = $this->matchedRoute['callback'];
            $extraArguments = $this->matchedRoute['arguments'];
        } else {
            $extraArguments = null;
        }

        \call_user_func_array($callback, [$this->request, $this->response, $extraArguments]);
    }

    private function methodIsValid(string $method)
    {
        return \in_array($method, $this->validMethod);
    }

    private function findMatchedRoute()
    {
        $method = $this->request->method;
        $uri = $this->request->uri;

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
        // var_dump($routeName);
        // var_dump($arguments);
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
// echo "Route: $route<br>";
// echo "URI: $uri<br>";
// echo "Pattern: $pattern<br>";
// print_r($matches);
// echo '<br>';

        return $matches;
    }

    private function findRouteParameters(string $route)
    {
        \preg_match_all('@{(.*?)}@', $route, $matches);
        // var_dump($matches);

        unset($matches[0]);

        $results = $this->unnestArrayRecursively(\array_values($matches));

        // var_dump(\array_values($matches));
        // var_dump($results);

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
// echo 'Input: ' . $route . '<br>';
// echo 'Output: ' . $result . '<br>';
        return $result;
    }
}