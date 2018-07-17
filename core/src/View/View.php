<?php

namespace Core\View;

class View
{
    private static $directory = \VIEW_DIR;

    private static $fileExtension = \VIEW_FILE_EXTENSION;

    public function __construct()
    {
        // if (! is_null($configs)) {
        //     $this->config($configs);
        //     return;
        // }

        // static::checkConfig();
    }

    // public static function config(array $configs)
    // {
    //     static::$directory = $configs['directory'];
    //     static::$fileExtension = $configs['file_extension'];
    // }

    // public function render(string $viewName, $data)
    // {
    //     echo static::get($viewName, $data);
    // }

    public function get(string $viewName, $data = null)
    {
        // static::checkConfig();
        // (new Debugger())->varDump(static::$directory, 'Directory');
        $filename = static::$directory . '/' . $viewName . '.' . static::$fileExtension;

        if (! file_exists($filename)) {
            throw new \Exception("Error: View does not exists.", 1);
            
        }

        if (! is_null($data)) {
            $data = (object) $data;
        }

        ob_start();
        include $filename;
        return ob_get_clean();
    }

    public function toJson($data)
    {
        return json_encode($data);
    }

    // public function dir(string $path)
    // {
    //     if (! \file_exists($path) || ! \is_dir($path)) {
    //         throw new \Exception("Error: Given directory path does not exists or not a directory, $path", 1);
            
    //     }

    //     static::$directory = $path;

    //     return $this;
    // }

    // private static function checkConfig()
    // {
    //     if (! empty(static::$directory) || ! empty(static::$fileExtension)) {
    //         return;
    //     }

    //     Config::loadModule('view');

    //     static::config([
    //         'directory' => Config::get('view.directory'),
    //         'file_extension' => Config::get('view.file_extension')
    //     ]);
    // }
}