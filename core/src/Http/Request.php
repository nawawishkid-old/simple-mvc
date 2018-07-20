<?php

namespace Core\Http;

class Request
{
    /**
     * @property array of information about HTTP Request.
     */
    private $info = [
        'header' => [],
        'method' => null,
        'uri' => null,
        'cookie' => null,
        'sessions' => [],
        'files' => [],
        'get' => [],
        'post' => []
    ];

    /**
     * Get $this->info
     * 
     * @api
     * 
     * @return mixed $this->info[$name]
     */
    public function __get(string $name)
    {
        return $this->info[$name];
    }

    /**
     * Set $this->info
     * 
     * For middleware to modify it
     * 
     * @api
     * 
     * @return void
     */
    public function __set(string $name, $value)
    {
        $this->info[$name] = $value;
    }
}