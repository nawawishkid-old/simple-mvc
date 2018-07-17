<?php

namespace Core\Http;

class Response
{
    private $status = [
        'code' => 200,
        'string' => 'OK'
    ];

    private $version = 1.1;

    private $header = [];

    private $data;

    private $isRedirected = false;

    public function __construct()
    {
        // $this->sessions = $sessions;
    }

    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    public function header(string $key, $value)
    {
        $this->header[$key] = $value;

        return $this;
    }

    public function status(int $code, string $string = null)
    {
        $this->status['code'] = $code;
        $this->status['string'] = $string;

        return $this;
    }

    public function version(float $version)
    {
        $this->version = $version;

        return $this;
    }

    public function redirect($route)
    {
        // var_dump($_SERVER['SERVER_NAME']);
        // var_dump($_SERVER['SERVER_PORT']);
        $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . '/' . $route;
        // $this->header('Location', $url);
        // $this->isRedirected = true;
        header('Location: ' . $url);
        exit();
    }

    // public function session($key, $value)
    // {
    //     if ()
    // }

    public function emit()
    {
        header($this->getComposedHTTPHeader());

        if ($this->isRedirected) {
            exit();
        }

        $type = gettype($this->data);

        switch ($type) {
            case 'object':
            case 'array':
                print_r($this->data);
                break;

            case 'string':
            case 'integer':
            case 'double':
                echo $this->data;
                break;
            
            default:
                # code...
                break;
        }
    }

    private function getComposedHTTPHeader()
    {
        $header = 'HTTP/' . $this->version . ' ' . $this->status['code'] . ' ' . $this->status['string'] . PHP_EOL;

        foreach ($this->header as $key => $value) {
            // echo $key . ': ' . $value . 'bbb';
            $header .= $key . ': ' . $value;// . PHP_EOL;
        }

        return $header;
    }
}