<?php

namespace Core\Http;

class Request
{
    private $info = [
        'header' => [],
        'method' => null,
        'uri' => null,
        'cookie' => null,
        'sessions' => [],
        'files' => [],
        'get' => [],
        'post' => []
        // 'data' => null,
    ];

    public function __construct()
    {
        // $this->header = getallheaders();
        // $this->method = $_SERVER['REQUEST_METHOD'];
        // $this->uri = $_SERVER['REQUEST_URI'];
        // $this->get = $_GET;
        // $this->post = $_POST;
        // $this->files = $_FILES;
        // $this->session = empty($_SESSION) ? [] : $_SESSION;
        // $this->cookie = $_COOKIE;
    }

    public function __get(string $name)
    {
        return $this->info[$name];
    }

    // For middleware to modify it
    public function __set(string $name, $value)
    {
        $this->info[$name] = $value;
    }
}