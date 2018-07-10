<?php

namespace Core\Output;

class View
{
    private $directory = '';

    private $fileExtension = '';

    public function __construct(array $configs)
    {
        if (! is_null($configs)) {
            $this->config($configs);
        }
    }

    public function config(array $configs)
    {
        $this->directory = $configs['directory'];
        $this->fileExtension = $configs['file_extension'];
    }

    public function get(string $viewName, $data)
    {
        ob_start();
        include $this->directory . '/' . $viewName . '.php';
        return ob_get_clean();
    }
}