<?php

namespace Core\Database;

use Core\Database\Connection;
use Core\Database\Query\Builder as QueryBuilder;
use Core\Support\Collection;

class Controller extends QueryBuilder
{
    public function __construct(Connection $databaseConnection)
    {
        $this->dbConnection = $databaseConnection;
        // $this->queryBuilder = $queryBuilder;
    }

    public function fetch()
    {
        $query = $this->dbConnection->fetch($this->get());
        $rows = new Collection();

        foreach ($query as $row) {
            $rows->push(new Collection($row));
        }

        $this->clear();

        return $rows;
    }
}