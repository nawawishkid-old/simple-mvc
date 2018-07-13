<?php

namespace Core\Database\Query\Concerns;

trait ConditionKeywords
{
    private $conditionalOperators = [];

    public function andWhere(string $column, string $operator, $value)
    {
        $this->addCondition('and', func_get_args(), 'where');
    }

    public function composeAndCondition($arguments, bool $prepared = false) 
    {
        $format = "AND %s %s %s";

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