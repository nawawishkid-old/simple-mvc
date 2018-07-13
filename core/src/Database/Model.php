<?php

namespace Core\Database;

use Core\Database\Controller as DatabaseController;
// use Core\Database\Query\Builder as QueryBuilder;

class Model
{
    protected $table;

    // DatabaseController instance
    private static $dbController;

    public function __construct(string $tableName, DatabaseController $databaseController)
    {
        $this->table = $tableName;
        self::$dbController = $databaseController;
    }

    public function all()
    {
        // 
        // DON'T KNOW HOW TO CHAIN QUERY BUILDER
        // 
        // self::$dbController->select('*')
        //         ->from($this->table)
        //         ->where($column, $operator, $value);
        self::$dbController
                ->table('wp_postmeta')
                ->select('*');

        // Return a Collection instance
        $data = self::$dbController->fetch();

        return $data;

        // $results = [];
        // $rows = $this->database->query($statement);
        
        // foreach ($rows as $row) {
        //     $results[] = $row;
        // }

        // return $rows;

        // $preparedStatement = $builder->getPrepared();
        // $this->database->prepare($preparedStatement)->execute();
    }
}