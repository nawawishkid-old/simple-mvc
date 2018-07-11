<?php

namespace Core\Input;

class Request
{
    private $info = [
        'header' => [],
        'method' => null,
        'uri' => null,
        'cookie' => null,
        'data' => null,
    ];

    public function __construct()
    {
        $this->header = getallheaders();
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->data = $_GET + $_POST;
        $this->cookie = $_COOKIE;
    }

    public function __get(string $name)
    {
        return $this->info[$name];
    }

    public function __set(string $name, $value)
    {
        $this->info[$name] = $value;
    }
}