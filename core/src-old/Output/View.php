<?php

namespace Core\Output;

use Core\Config;
use Core\Support\Debugger;

class View
{
    private static $directory = '';

    private static $fileExtension = '';

    public function __construct(array $configs = null)
    {
        if (! is_null($configs)) {
            $this->config($configs);
            return;
        }

        self::checkConfig();
    }

    public static function config(array $configs)
    {
        self::$directory = $configs['directory'];
        self::$fileExtension = $configs['file_extension'];
    }

    public function render(string $viewName, $data)
    {
        echo self::get($viewName, $data);
    }

    public function get(string $viewName, $data)
    {
        self::checkConfig();
        // (new Debugger())->varDump(self::$directory, 'Directory');
        $data = (object) $data;

        ob_start();
        include self::$directory . '/' . $viewName . '.php';
        return ob_get_clean();
    }

    // public function dir(string $path)
    // {
    //     if (! \file_exists($path) || ! \is_dir($path)) {
    //         throw new \Exception("Error: Given directory path does not exists or not a directory, $path", 1);
            
    //     }

    //     self::$directory = $path;

    //     return $this;
    // }

    private static function checkConfig()
    {
        if (! empty(self::$directory) || ! empty(self::$fileExtension)) {
            return;
        }

        Config::loadModule('view');

        self::config([
            'directory' => Config::get('view.directory'),
            'file_extension' => Config::get('view.file_extension')
        ]);
    }
}