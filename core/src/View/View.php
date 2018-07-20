<?php

namespace Core\View;

class View
{
    /**
     * @property string Path of views directory.
     */
    private static $directory = \VIEW_DIR;

    /**
     * @property string View file extension.
     */
    private static $fileExtension = \VIEW_FILE_EXTENSION;

    /**
     * Return View content.
     * 
     * @api
     * 
     * @uses View::getFilePathByName()
     * @uses View::getViewContentFromFilePath()
     * 
     * @param string $viewName View name.
     * @param array $data Data array to be used as an object in the view.
     * 
     * @return string View content.
     */
    public function get(string $viewName, array $data = null)
    {
        $filePath = static::getFilePathByName($viewName);

        if (! file_exists($filePath)) {
            throw new \Exception("Error: View does not exists.", 1);
            
        }

        if (! is_null($data)) {
            $data = (object) $data;
        }

        return static::getViewContentFromFilePath($filePath);
    }

    /**
     * Return path of the given view name.
     * 
     * @param string $name Name of the view.
     * 
     * @return string File path
     */
    private function getFilePathByName(string $name)
    {
        return static::$directory . '/' . $name . '.' . static::$fileExtension;
    }

    /**
     * Return View content.
     * 
     * @param string $filePath Path of the view file.
     * 
     * @return string View content.
     */
    private function getViewContentFromFilePath(string $filePath)
    {
        // Not good practice, may use too much memory.
        // var_dump(memory_get_usage());
        ob_start();
        include $filePath;
        $result = ob_get_clean();

        // var_dump(memory_get_usage());

        return $result;
    }

    public function toJson($data)
    {
        return json_encode($data);
    }
}