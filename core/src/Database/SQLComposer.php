<?php

namespace Core\Database;

use Core\Support\Debugger;

class SQLComposer
{
    public static $baseKeywords = [
        'select' => [],
        'insert_into' => [],
        'update' => [],
        'delete_from' => []
    ];

    public static $subKeywords = [
        'where',
        'having',
        'on'
    ];

    private static $validOperator = [
        '=',
        '<>',
        '<',
        '>',
        '<=',
        '>=',
        'between',
        'like',
        'in'
    ];

    private static $selectedBaseKeyword;

    private static $isFirstKeyword = true;

    private static $prevKeyword;

    protected static $inputs = [
        'insert_into' => [],
        'update' => [],
        'delete_from' => []
    ];

    /**
     * Compose SQL statement
     * 
     * @api
     */
    public static function compose(string $baseKeywords = null)
    {
        $select = \is_null($baseKeywords) ? self::$selectedBaseKeyword : $baseKeywords;

        // (new Debugger())->varDump(self::$baseKeywords, "SQLCompose::compose() \self::$baseKeywords");

        return \implode('', self::$baseKeywords[$select]);
    }

    /**
     * Add SQL 'SELECT column_1, column_n FROM table' statement.
     * 
     * @api
     */
    public static function select(array $columns, string $table)
    {
        $columnString = implode(', ', $columns);
        $stmt = "SELECT $columnString FROM $table" . PHP_EOL;

        self::addKeyword('select', $stmt);

        return self;
    }

    /**
     * Add SQL 'INSERT INTO' statement.
     * 
     * @api
     */
    public static function insert(string $table, array $columns = [], array $values)
    {
        self::addInputs('insert_into', $values);
        // self::$inputs['insert_into'] = $values;

        // if (self::$isFirstKeyword) {
        //     $columnString = \implode(', ', $columns);
        //     $insert = "INSERT INTO $table ($columnString) VALUES";
        // } else {
        //     $insert = ',';
        // }

        $columnString = \implode(', ', $columns);
        $preparedValues = self::formatInsertValues($columns, $values);

        // var_dump($preparedValues);
        
        $stmt = "INSERT INTO $table ($columnString) VALUES ($preparedValues)";

        // (new Debugger())->varDump($stmt, "SQLCompose::insert() statement");

        self::addKeyword('insert_into', $stmt, true);

        return self;
    }

    /**
     * Add SQL 'UPDATE' statement.
     * 
     * @api
     */
    public static function update(string $table, array $setClauseValues)
    {
        self::addInputs('update', $setClauseValues);

        $setClause = self::composeUpdateSetClause($setClauseValues);
        $stmt = "UPDATE $table SET $setClause";

        self::addKeyword('update', $stmt, true);

        return self;
    }

    public static function deleteFrom(string $table)
    {
        $stmt = "DELETE FROM $table" . PHP_EOL;
        
        self::addKeyword('delete_from', $stmt, true);

        return self;
    }

    /**
     * Add SQL 'WHERE' clause into statement.
     * 
     * @api
     */
    // Add where clause to baseKeyword
    public static function where(string $column, string $operator, $value)
    {
        self::addInputs(self::$selectedBaseKeyword, $value);
        // self::$inputs[self::$selectedBaseKeyword][] = self::validateValue($value);

        $where = (self::$prevKeyword === 'where') ? 'AND' : 'WHERE';
        $statement = self::composeOperatorStatement($column, $operator, $value);
        $statement = "{$where} {$statement}" . PHP_EOL;
        
        self::addKeyword('where', $statement);

        return self;
    }

    /**
     * Add SQL 'OR' keyword into previously added clause.
     * 
     * @api
     */
    public static function orWhere(string $column, string $operator, $value)
    {
        self::prevKeywordIsMatched(['where']);
        self::addInputs(self::$selectedBaseKeyword, $value);

        $statement = self::composeOperatorStatement($column, $operator, $value);
        $statement = "OR $statement" . PHP_EOL;
        
        self::addKeyword('or_where', $statement);

        return self;
    }

    protected static function addInputs(string $baseKeyword, $inputs)
    {
        if (! \is_array($inputs)) {
            self::$inputs[$baseKeyword][] = self::validateValue($inputs);
            
            return;
        }

        array_walk_recursive($inputs, function ($value, $key) use ($baseKeyword) {
            // echo $value . '<br>';
            self::$inputs[$baseKeyword][] = self::validateValue($value);
        });
    }

    /**
     * Add any SQL keyword for later composition.
     */
    protected static function addKeyword(string $keyword, $value, bool $forceFirstKeyword = false)
    {
        if ($forceFirstKeyword) {
            self::$isFirstKeyword = true;
        }

        if (self::$isFirstKeyword) {
            self::$isFirstKeyword = false;
            if (! self::isBaseKeyword($keyword)) {
                throw new \Exception("Error: Base keyword required i.e. " . implode(', ', self::$baseKeywords), 1);
                
            }

            self::$selectedBaseKeyword = $keyword;
            self::$baseKeywords[$keyword][] = $value;
        } else {
            self::$baseKeywords[self::$selectedBaseKeyword][] = $value;
        }

        self::$prevKeyword = $keyword;
    }

    protected static function resetStatement()
    {
        self::resetInputArray();
        // self::resetBaseKeywordArray();
    }

    /**
     * Compose SQL 'SET' clause for 'UPDATE' statement.
     * 
     * @param array $setClauseValues Associative array where the key is a column name and the value is value to update
     */
    private static function composeUpdateSetClause(array $setClauseValues)
    {
        \array_walk($setClauseValues, function (&$value, $key) {
            // $value = self::validateValue($value);
            $value = "$key = ?" . PHP_EOL;
        });

        return implode(', ', $setClauseValues);
    }

    private static function resetInputArray()
    {
        self::$inputs = [];
    }

    private static function resetBaseKeywordArray()
    {
        self::$baseKeywords = \array_map(function ($item) {
            return [];
        }, self::$baseKeywords);
    }

    private static function formatInsertValues(array $columns, array $values)
    {
        $preparedValuesArray = \array_replace($values, \array_fill(0, count($values), '?'));

        $columnsLength = count($columns);
        $valuesLength = count($values);

        if ($valuesLength > $columnsLength) {
            $preparedValues = '';

            foreach ($preparedValuesArray as $key => $value) {
                $rowIsEnd = (($key + 1) % $columnsLength) === 0;
                $isLastRow = ($key + 1) === $valuesLength;

                if (
                    $key > 0 
                    && $key < $valuesLength 
                    && $rowIsEnd
                ) {
                    $suffix = $isLastRow ? '' : '), (';
                    $preparedValues .= $preparedValuesArray[$key] . $suffix;
                    
                    continue;
                }

                $preparedValues .= $preparedValuesArray[$key] . ', ';
            }
        } else {
            $preparedValues = implode(', ', $preparedValuesArray);
        }

        return $preparedValues;
    }

    private static function validateValue($value)
    {
        $value = \is_string($value) ? '"' . \trim($value) . '"' : $value;

        return $value;
    }

    private static function isBaseKeyword(string $keyword)
    {
        return empty(self::$baseKeywords[$keyword]);
    }

    private static function callIfCallable($mayCallable, $arguments)
    {
        if (is_callable($mayCallable)) {
            return \call_user_func_array($mayCallable, (array) $arguments);
        }

        return $mayCallable;
    }

    private static function composeOperatorStatement(string $field, string $operator, string $input)
    {
        self::addInputs(self::$prevKeyword, $input);

        $statement = "$field $operator ?";

        if (! self::keywordOperatorIsValid($operator)) {
            throw new \Exception("Error: Invalid query operator, $operator on '$statement'", 1);
            
        }

        return $statement;
    }

    private static function prevKeywordIsMatched(array $keywords)
    {
        if (! \in_array(self::$prevKeyword, $keywords)) {
            throw new \Exception("Error: Keyword $keywords is required first.", 1);
            
        }

        return true;
    }

    private static function keywordAdded(array $keywords, bool $requiredAll = true)
    {
        $found = false;

        foreach ($keywords as $key => $keyword) {
            if (empty(self::$queryStructure[$keyword])) {
                if ($requiredAll) {
                    return false;
                } else {
                    continue;
                }
            }

            $found = true;
        }

        return $found;
    }

    private static function queryKeywordIsValid(string $keyword)
    {
        return \in_array($keyword, \array_keys(self::$queryStructure));
    }

    private static function keywordOperatorIsValid(string $operator)
    {
        return \in_array(\strtolower($operator), \array_keys(self::$validOperator));
    }
}