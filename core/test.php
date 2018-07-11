<?php

class DB
{
    private static $data = [
        'name' => 'Nawawish',
        'surname' => 'Samerpark'
    ];

    public function select($key)
    {
        return self::$data[$key];
    }

    public function update()
    {

    }
}

var_dump(DB::select('name'));

$db = new DB();
var_dump($db->select('name'));