<?php

namespace Core\CoreInterface;

interface RDBManager
{
    public function connect(string $host, string $dbName, string $username, string $password);

    public function close();

    // public function create(string $tableName);

    // public function table();

    public function insert(string $tableName, array $fields, array $values);

    public function update();

    public function delete();

    public function where();

    public function limit();

    public function orderBy();

    public function groupBy();
}