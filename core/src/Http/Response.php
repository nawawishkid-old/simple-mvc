<?php

namespace Core\Http;

class Response
{
    /**
     * @property array Array contains HTTP status code and its description.
     */
    private $status = [
        'code' => 200,
        'description' => 'OK'
    ];

    /**
     * @property int|float Version of HTTP protocal.
     */
    private $version = 1.1;

    /**
     * @property array Array of HTTP header.
     */
    private $header = [];

    /**
     * @property mixed Data to be sent back to the client.
     */
    private $data;

    /**
     * Set data to be sent back to the client.
     * 
     * @api
     * 
     * @param mixed $data Data to be sent back to the client.
     * 
     * @return $this
     */
    public function data($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Set HTTP Header.
     * 
     * @api
     * 
     * @param string $key HTTP Header key.
     * @param mixed $value HTTP Header value.
     * 
     * @return $this
     */
    public function header(string $key, $value)
    {
        $this->header[$key] = $value;

        return $this;
    }

    /**
     * Set HTTP Response status.
     * 
     * @api
     * 
     * @param int $code HTTP Response status code.
     * @param string $description HTTP Response status description.
     * 
     * @return $this
     */
    public function status(int $code, string $description = null)
    {
        $this->status['code'] = $code;
        $this->status['description'] = $description;

        return $this;
    }

    /**
     * Set version of HTTP.
     * 
     * @api
     * 
     * @param int|float $version Version of HTTP protocal i.e. 1.1, 2.
     * 
     * @return $this
     */
    public function version(numeric $version)
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Redirect by setting HTTP Response header.
     * 
     * @api
     * 
     * @param string $route URL to redirect to.
     * 
     * @return void
     */
    public function redirect($route)
    {
        // $url = 'http://' . $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . '/' . $route;
        header('Location: ' . $route);
        exit();
    }

    /**
     * Echo or print_r the $this->data based on the data type.
     * 
     * @api
     * 
     * @return void
     */
    public function emit()
    {
        header($this->getComposedHTTPHeader());

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

    /**
     * Compose and return HTTP header string.
     * 
     * @return string HTTP header.
     */
    private function getComposedHTTPHeader()
    {
        $header = 'HTTP/' . $this->version . ' ' . $this->status['code'] . ' ' . $this->status['description'] . PHP_EOL;

        foreach ($this->header as $key => $value) {
            $header .= $key . ': ' . $value;
        }

        return $header;
    }
}