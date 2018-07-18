<?php

namespace Core\Router;

use Core\Http\Request;
use Core\Http\Response;
use Core\Controller;
use Core\Router\Middleware;

class Router
{
    /**
     * @property Request instance of Core\Http\Request.
     */
    private $request;
    /**
     * @property Response instance of Core\Http\Response.
     */
    private $response;

    /**
     * @property array Array of registered URL.
     */
    private $registeredRoutes = [];

    /**
     * @property string Previously registered HTTP method.
     */
    private $previousRegisteredMethod;
    /**
     * @property callable|closure Callback for unregistered route/URL.
     */
    private $notFoundCallback;
    /**
     * @property array Array of information about matched route that matched with current request URL.
     */
    private $matchedRoute = [
        'route' => null,
        'method' => null,
        'arguments' => [],
        'callback' => null
    ];
    /**
     * @property array Array of valid HTTP methods.
     */
    private $validMethods = [
        'GET',
        'POST',
        'PUT',
        'DELETE',
        'PATCH',
        'OPTIONS'
    ];

    /**
     * Set $this->request and $this->response.
     * 
     * @api
     * 
     * @param Request $request Instance of Request.
     * @param Response $request Instance of Response.
     * 
     * @return void
     */
    public function __construct(Request $request, Response $response)
    {
        $this->request = $request;
        $this->response = $response;
    }

    /**
     * Resolve current request URL to be matched with registered route/URL.
     * 
     * @api
     * 
     * @uses Router::addPrefixSlash()
     * @uses Router::methodIsValid()
     * @uses Router::findMatchedRoute()
     * @uses Router::executeCallback()
     * @uses Router::urlToRegex()
     * @uses Router::executeMiddlewares()
     * 
     * @return mixed Result of registered route's callback.
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

        $modifiedUri = $this->urlToRegex($uri);

        if (! empty($this->getMiddlewares($request->method, $modifiedUri))) {
            $middleware = $this->executeMiddlewares($request->method, $modifiedUri);

            if (! $middleware) {
                return;
            }
        }
        
        return $this->executeCallback();
    }

    /**
     * Execute middleware callback for the given HTTP method and route/URL.
     * 
     * @uses Router::getMiddlewares()
     * @uses Router::executeCallback()
     * 
     * @param string $method HTTP method.
     * @param string $route Route/URL of the registered route/URL.
     * 
     * @return bool True if the request passed the middleware, otherwise false.
     */
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
     * Get array of middlewares of given route.
     * 
     * @param string $method HTTP request method for the middleware.
     * @param string $route Modified URI of the middleware.
     * 
     * @return mixed Array of middlewares callback or null if there is no middleware for the given route\URL.
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
     * Add middleware to the previously registered route.
     * 
     * @api
     * 
     * @uses Router::throwTypeIsValid()
     * 
     * @param callable|closure|string $middleware Name of middleware or callback to be applied.
     * 
     * @return $this
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
     * Register route/URL for HTTP 'GET' method.
     * 
     * @uses Router::registerRoute()
     * 
     * @param string $route Route/URL to be registered.
     * @param callable|closure $callback Callback to be applied when the request URL is matched.
     * 
     * @return $this
     */
    public function get(string $route, $callback)
    {
        $this->registerRoute('GET', $route, $callback);

        return $this;
    }

    /**
     * Register route/URL for HTTP 'POST' method.
     * 
     * @uses Router::registerRoute()
     * 
     * @param string $route Route/URL to be registered.
     * @param callable|closure $callback Callback to be applied when the request URL is matched.
     * 
     * @return $this
     */
    public function post(string $route, $callback)
    {
        $this->registerRoute('POST', $route, $callback);

        return $this;
    }

    /**
     * Register route/URL for HTTP 'PUT' method.
     * 
     * @api
     * 
     * @uses Router::registerRoute()
     * 
     * @param string $route Route/URL to be registered.
     * @param callable|closure $callback Callback to be applied when the request URL is matched.
     * 
     * @return $this
     */
    public function put(string $route, $callback)
    {
        $this->registerRoute('PUT', $route, $callback);

        return $this;
    }

    /**
     * Register route/URL for HTTP 'DELETE' method.
     * 
     * @api
     * 
     * @uses Router::registerRoute()
     * 
     * @param string $route Route/URL to be registered.
     * @param callable|closure $callback Callback to be applied when the request URL is matched.
     * 
     * @return $this
     */
    public function delete(string $route, $callback)
    {
        $this->registerRoute('DELETE', $route, $callback);

        return $this;
    }

    /**
     * Register route/URL for HTTP 'PATCH' method.
     * 
     * @api
     * 
     * @uses Router::registerRoute()
     * 
     * @param string $route Route/URL to be registered.
     * @param callable|closure $callback Callback to be applied when the request URL is matched.
     * 
     * @return $this
     */
    public function patch(string $route, $callback)
    {
        $this->registerRoute('PATCH', $route, $callback);

        return $this;
    }

    /**
     * Register route/URL for HTTP 'OPTIONS' method.
     * 
     * @api
     * 
     * @uses Router::registerRoute()
     * 
     * @param string $route Route/URL to be registered.
     * @param callable|closure $callback Callback to be applied when the request URL is matched.
     * 
     * @return $this
     */
    public function options(string $route, $callback)
    {
        $this->registerRoute('OPTIONS', $route, $callback);

        return $this;
    }

    /**
     * Add given callback to $this->notFoundCallback. Call this callback when there is no matched route/URL.
     * 
     * @api
     * 
     * @param callable|closure $callback Callback to be called when there is no matched route.
     * 
     * @return $this
     */
    public function notFound($callback)
    {
        $this->notFoundCallback = $callback;

        return $this;
    }

    /**
     * Register route/URL for given HTTP method.
     * 
     * @api
     * 
     * @uses Router::throwTypeIsValid()
     * @uses Router::methodIsValid()
     * @uses Router::urlToRegex()
     * 
     * @param string $method HTTP method.
     * @param string $route Route/URL to be registered.
     * @param callable|closure $callback Callback to be applied when the request URL is matched.
     * 
     * @return void
     */
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
            'callback' => $callback
        ];
        
        $this->registeredRoutes[$method][$this->urlToRegex($route)] = $routeInfo;
    }

    /**
     * Execute callback of the matched route/URL ($this->matchedRoute).
     * 
     * @uses Router::throwTypeIsValid()
     * @uses Controller::class
     * 
     * @return mixed Result of matched route's callback.
     */
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

    /**
     * Find registered route by using HTTP Request URI.
     * 
     * @uses Route::matchRouteWithURI()
     * @uses Route::addMatchedRouteInfo()
     * 
     * @return void
     */
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

    /**
     * Add matched route information from given arguments to $this->matchedRoute.
     * 
     * @uses Route::findRouteParameters
     * 
     * @param string $route Route/URI that matched with the current HTTP Request URI.
     * @param string $method HTTP method.
     * @param array $arguments Array of HTTP Request URI arguments.
     * 
     * @return void
     */
    private function addMatchedRouteInfo(string $route, string $method, array $arguments)
    {
        $this->matchedRoute['route'] = $route;
        $this->matchedRoute['method'] = $method;
        $this->matchedRoute['callback'] = $this->registeredRoutes[$method][$route]['callback'];

        $routeParameters = $this->findRouteParameters($this->registeredRoutes[$method][$route]['originalRoute']);

        foreach ($arguments as $index => $argument) {
            $this->matchedRoute['arguments'][$routeParameters[$index]] = $argument;
        }
    }

    /**
     * Match given route with given HTTP Request URI using preg_match().
     * 
     * @param string $route Route to be matched with Request URI.
     * @param string $uri URI of HTTP Request.
     * 
     * @return array Matched route in array.
     */
    private function matchRouteWithURI(string $route, string $uri)
    {
        $pattern = '@^' . $route . '/?$@';
        preg_match($pattern, $uri, $matches);

        return $matches;
    }

    /**
     * Check whether given route has placeholder for parameter, then extract the parameter accordingly.
     * 
     * @uses Route::unnestArrayRecursively()
     * 
     * @param string $route Route/URL to be searched for parameter.
     * 
     * @return array Array of parameters.
     */
    private function findRouteParameters(string $route)
    {
        preg_match_all('@{(.*?)}@', $route, $matches);

        unset($matches[0]);

        $results = $this->unnestArrayRecursively(array_values($matches));

        return $results;
    }

    /**
     * Unnest given array recursively.
     * 
     * @param array Array to be unnested.
     * 
     * @return array Unnested array.
     */
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

    // ============================= Given-route modification =============================
    /**
     * Add prefix slash, to given URL, then convert it to regex pattern.
     * 
     * @uses Router::addPrefixSlash()
     * @uses Router::convertRouteToRegexPattern()
     * 
     * @param string $route Route/URL to be converted.
     * 
     * @return string RegExp string.
     */
    private function urlToRegex(string $route)
    {
        return $this->convertRouteToRegexPattern($this->addPrefixSlash($route));
    }

    /**
     * Add prefix slash to the given string.
     * 
     * @param string $string String to be prefixed with slash '/'.
     * 
     * @return string Prefixed string.
     */
    private function addPrefixSlash(string $string)
    {
        if (mb_substr($string, 0, 1) !== '/') {
            $string = '/' . $string;
        }

        return $string;
    }

    /**
     * Convert given route to pre-defined Regular Expression pattern.
     * 
     * @param string $route Route/URL to be converted.
     * @param bool $required Tell whether given route's parameter is required or not.
     * 
     * @return string Regular Expression pattern.
     */
    private function convertRouteToRegexPattern(string $route, bool $required = true)
    {
        $modifier = $required ? '+' : '*';

        $result = preg_replace('/{.*?}/', '(\w' . $modifier . ')', $route);

        return $result;
    }

    // ============================== Validations ==============================
    /**
     * Throw an error if the type of given data is not one of given data-types array.
     * 
     * @uses Router::typeIsValid()
     * 
     * @param mixed $var Any data to be checked.
     * @param array $types Array of data types.
     * 
     * @return bool True
     */
    private function throwTypeIsValid($var, array $types)
    {
        if (! $this->typeIsValid($var, $types)) {
            throw new \Exception("Error: Invalid argument types; Valid type(s) is/are " . implode(', ', $types) . "; " . gettype($var) . " given", 1);
            
        }

        return true;
    }

    /**
     * Check whether the type of given data is one of given data-type array.
     * 
     * @param mixed $var Any data to be checked.
     * @param array $types Array of data types.
     * 
     * @return bool
     */
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

    /**
     * Check whether given method is a valid HTTP method.
     * 
     * @param string $method HTTP method.
     * 
     * @return bool
     */
    private function methodIsValid(string $method)
    {
        return in_array($method, $this->validMethods);
    }
}