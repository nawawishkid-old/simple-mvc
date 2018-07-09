<?php

namespace Core;

class View
{
    private $directory = '';

    private $fileExtension = '';

    public function __construct(array $configs)
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