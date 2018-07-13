<?php

namespace Core\User\API;

use Core\Database\Model as DatabaseModel;

class Model
{
    private static $model;
    
    public static function __callStatic(string $methodName, $arguments)
    {
        return \call_user_func_array([static::$model, $methodName], $arguments);
    }

    public static function initial(DatabaseModel $model)
    {
        static::$model = $model;
    }

    public static function where() {}
}