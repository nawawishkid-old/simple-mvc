<?php

namespace Core;

class Response
{
    private $status = [
        'code' => 200,
        'string' => 'OK'
    ];

    private $version = 1.1;

    private $header = [];

    private $data;

    public function __construct(array $configs = null)
    {

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

    public function emit()
    {
        // var_dump($this->getComposedHTTPHeader());
        // exit;
        \header($this->getComposedHTTPHeader());

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
            $header .= $key . ': ' . $value . PHP_EOL;
        }

        return $header;
    }
}