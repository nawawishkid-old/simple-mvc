<?php

namespace Core\Output;

use Core\Config;
use Core\Support\Debugger;

class View
{
    private $directory = '';

    private $fileExtension = '';

    public function __construct(array $configs = null)
    {
        if (! is_null($configs)) {
            $this->config($configs);
            return;
        }

        $this->config([
            'directory' => Config::get('view.directory'),
            'file_extension' => Config::get('view.file_extension')
        ]);
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

    public function dir(string $path)
    {
        if (! \file_exists($path) || ! \is_dir($path)) {
            throw new \Exception("Error: Given directory path does not exists or not a directory, $path", 1);
            
        }

        $this->directory = $path;

        return $this;
    }
}