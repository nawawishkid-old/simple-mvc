<?php

namespace Core\Database\Query\Concerns;

trait ConditionKeywords
{
    // private $conditionalOperators = [];

    /**
     * Add SQL 'AND' condition to 'WHERE' conjunction of $this->queryVerbs[$verb].
     * 
     * @param string $column Column name.
     * @param string $operator SQL comparable operator.
     * @param mixed $value Value of the given column.
     * 
     * @return $this
     */
    public function andWhere(string $column, string $operator, $value)
    {
        $this->addCondition('and', func_get_args(), 'where');

        return $this;
    }

    /**
     * Compose SQL 'AND' statement for 'WHERE' conjunction.
     * 
     * @param array $arguments Array of 'AND' arguments
     * @param bool $prepared Whether to prepare the query or not.
     * 
     * @return string SQL query.
     */
    public function composeAndCondition($arguments, bool $prepared = false) 
    {
        $format = " AND %s %s %s";

        $arguments[2] = $prepared ? '?' : $this->validateValue($arguments[2]);
        
        return sprintf($format, $arguments[0], $arguments[1], $arguments[2]);
    }

    /**
     * Add SQL 'OR' condition to 'WHERE' conjunction of $this->queryVerbs[$verb].
     * 
     * @param string $column Column name.
     * @param string $operator SQL comparable operator.
     * @param mixed $value Value of the given column.
     * 
     * @return $this
     */
    public function orWhere(string $column, string $operator, $value)
    {
        $this->addCondition('or', func_get_args(), 'where');
    }

    /**
     * Compose SQL 'OR' statement for 'WHERE' conjunction.
     * 
     * @param array $arguments Array of 'OR' arguments
     * @param bool $prepared Whether to prepare the query or not.
     * 
     * @return string SQL query.
     */
    public function composeOrCondition($arguments, bool $prepared = false) 
    {
        $format = " OR %s %s %s";

        $arguments[2] = $prepared ? '?' : $this->validateValue($arguments[2]);
        
        return sprintf($format, $arguments[0], $arguments[1], $arguments[2]);
    }
}