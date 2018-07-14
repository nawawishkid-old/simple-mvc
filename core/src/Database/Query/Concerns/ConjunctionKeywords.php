<?php

namespace Core\Database\Query\Concerns;

trait ConjunctionKeywords
{
    private $conditionalOperators = [];

    public function where(string $column, string $operator, $value)
    {
        $this->addConjunction(
            'where', 
            func_get_args(), 
            ['select', 'update', 'delete'],
            ['and', 'or']
        );

        return $this;
    }

    private function composeWhereConjunction($arguments, bool $prepared = false)
    {
        $format = " WHERE %s %s %s"; // . implode(' ', $arguments);

        $arguments[2] = $prepared ? '?' : $this->validateValue($arguments[2]);
        
        return sprintf($format, $arguments[0], $arguments[1], $arguments[2]);
    }

    // public function insert($tableName) {}
    // public function update($tableName) {}
    // public function delete($tableName) {}
    // public function join($tableName) {}
    // public function innerJoin($tableName) {}
    // public function leftJoin($tableName) {}
    // public function rightJoin($tableName) {}
    // public function union($tableName) {}
    // public function unionAll($tableName) {}

    // public function composeSelectStatement($data) 
    // {
    //     $format = "SELECT %s FROM %s";
    //     $
    // }

    // public function composeInsertStatement($data) {}
    // public function composeUpdateStatement($data) {}
    // public function composeDeleteStatement($data) {}
    // public function composeJoinStatement($data) {}
    // public function composeInnerJoinStatement($data) {}
    // public function composeLeftJoinStatement($data) {}
    // public function composeRightJoinStatement($data) {}
    // public function composeUnionStatement($data) {}
    // public function composeUnionAllStatement($data) {}
}