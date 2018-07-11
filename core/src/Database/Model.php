<?php

namespace Core\Database;

abstract class Model
{
    public static function initial()
    {
        self::$db = $database;
    }
}