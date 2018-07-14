<?php

namespace Core\Database\Query\Concerns;

trait VerbKeywords
{
    public function select($columns)
    {
        $columns = is_array($columns) ? $columns : func_get_args();

        $this->addVerb(
            'select', 
            $columns,
            [
                'join', 'innerJoin', 'leftJoin',
                'rightJoin', 'groupBy', 'orderBy'
            ],
            [],
            ['where']
        );

        return $this;
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

    public function composeSelectVerb($arguments, bool $prepared = false) 
    {
        $format = "SELECT %s FROM %s";

        return sprintf($format, implode(', ', $arguments), $this->table);
    }

    // public function composeInsertVerb($data) {}
    // public function composeUpdateVerb($data) {}
    // public function composeDeleteVerb($data) {}
    // public function composeJoinVerb($data) {}
    // public function composeInnerJoinVerb($data) {}
    // public function composeLeftJoinVerb($data) {}
    // public function composeRightJoinVerb($data) {}
    // public function composeUnionVerb($data) {}
    // public function composeUnionAllVerb($data) {}
}