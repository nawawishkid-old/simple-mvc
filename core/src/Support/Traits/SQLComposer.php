<?php

namespace Core\Support\Traits;

trait SQLComposer
{
    protected $baseKeywords = [
        'select',
        'insert_into',
        'update',
        'delete_from'
    ];

    protected $subKeywords = [
        'where',
        'having',
        'on'
    ];

    private $selectedBaseKeyword;

    private $isFirstKeyword = true;

    private $input = [];

    public function compose()
    {
        return \implode('', $this->baseKeywords[$this->selectedBaseKeyword]);
    }

    private function addKeyword(string $keyword, $value)
    {
        if ($this->isFirstKeyword) {
            $this->isFirstKeyword = false;
            if (! $this->isBaseKeyword($keyword)) {
                throw new \Exception("Error: Base keyword required i.e. " . implode(', ', $this->baseKeywords), 1);
                
            }

            $this->selectedBaseKeyword = $keyword;
            $this->baseKeywords[$keyword][] = $value;
        } else {
            $this->baseKeywords[$this->selectedBaseKeyword][] = $value;
        }

        $this->prevKeyword = $keyword;
    }

    public function select(array $columns, string $table)
    {
        $columnString = implode(', ', $columns);
        $value = "SELECT $columnString FROM $table" . PHP_EOL;

        $this->addKeyword('select', $value);

        return $this;
    }

    // ควรให้ $values เป็น callable ได้ด้วย?
    public function insert(string $table, array $columns = [], array $values)
    {
        // $this->callIfCallable($values)
        $this->input = $values;

        if ($this->isFirstKeyword) {
            $columnString = \implode(', ', $columns);
            $insert = "INSERT INTO $table ($columnString) VALUES";
        } else {
            $insert = ',';
        }

        $preparedValues = $this->formatInsertValues($columns, $values);

        // var_dump($preparedValues);
        
        $stmt = "$insert ($preparedValues)";

        // var_dump($stmt);

        $this->addKeyword('insert_into', $stmt);

        return $this;
    }

    public function where(string $column, string $operator, $value)
    {
        $where = ($this->prevKeyword === 'where') ? 'AND' : 'WHERE';
        $statement = $this->composeOperatorStatement($column, $operator, $value);
        $statement = "{$where} {$statement}" . PHP_EOL;
        
        $this->addKeyword('where', $statement);

        return $this;
    }

    public function orWhere(string $column, string $operator, $value)
    {
        $this->prevKeywordIsMatched(['where']);

        $statement = $this->composeOperatorStatement($column, $operator, $value);
        $statement = "OR $statement" . PHP_EOL;
        
        $this->addKeyword('or_where', $statement);

        return $this;
    }

    private function resetInputArray()
    {
        $this->input = [];
    }

    private function resetBaseKeywordArray()
    {
        $this->baseKeywords = \array_map(function ($item) {
            return [];
        }, $this->baseKeyword);
    }

    private function formatInsertValues(array $columns, array $values)
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

    private function isBaseKeyword(string $keyword)
    {
        return empty($this->baseKeywords[$keyword]);
    }

    private function callIfCallable($mayCallable, $arguments)
    {
        if (is_callable($mayCallable)) {
            return \call_user_func_array($mayCallable, (array) $arguments);
        }

        return $mayCallable;
    }

    // protected $queryStructure = [
    //     'select' => '',
    //     'insert_into' => '',
    //     'update' => '',
    //     'delete_from' => '',
    //     'from' => [],
    //     'set' => [],
    //     'where' => '',
    //     'distinct' => '',
    //     'limit' => '',
    //     'join' => [],
    //     'order_by' => [],
    //     'group_by' => [],
    //     'join' => [],
    //     'on' => ''
    // ];

    private $validOperator = [
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

    // private $prevKeyword;

    // // Composition
    // protected function compose()
    // {

    // }

    // private function composeInsertInto(string $table, array $values)
    // {
    //     $fields = implode(', ', \array_keys($values));
    //     $values = implode(', ', \array_values($values));
        
    //     return "INSERT INTO $table ($fields) VALUES ($values);";
    // }

    private function composeOperatorStatement(string $field, string $operator, string $input)
    {
        $this->input[] = $input;

        $statement = "$field $operator ?";

        if (! $this->keywordOperatorIsValid($operator)) {
            throw new \Exception("Error: Invalid query operator, $operator on '$statement'", 1);
            
        }

        return $statement;
    }

    // private function addQueryKeyword(
    //     string $keyword, 
    //     $value, 
    //     array $required = null, 
    //     bool $requiredAll = true
    // )
    // {
    //     if (! is_null($required)) {
    //         if (! $this->keywordAdded($required, $requiredAll)) {
    //             throw new \Exception("Error: Cannot add query $keyword $value, 
    //             following statement is required: " . \implode(', ', $required), 1);
                
    //         }
    //     }

    //     if (! $this->queryKeywordIsValid($keyword)) {
    //         throw new \Exception("Error: Invalid query keyword, $keyword", 1);
            
    //     }

    //     $this->queryStructure[$keyword] = $value;
    //     $this->prevKeyword = $keyword;
    // }

    private function prevKeywordIsMatched(array $keywords)
    {
        if (! \in_array($this->prevKeyword, $keywords)) {
            throw new \Exception("Error: Keyword $keywords is required first.", 1);
            
        }

        return true;
    }

    private function keywordAdded(array $keywords, bool $requiredAll = true)
    {
        $found = false;

        foreach ($keywords as $key => $keyword) {
            if (empty($this->queryStructure[$keyword])) {
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

    private function queryKeywordIsValid(string $keyword)
    {
        return \in_array($keyword, \array_keys($this->queryStructure));
    }

    private function keywordOperatorIsValid(string $operator)
    {
        return \in_array(\strtolower($operator), \array_keys($this->validOperator));
    }

    // Key action
    // public function select(array $fields)
    // {
    //     $this->addQueryKeyword('select', $fields);
    // }

    // /**
    //  * [
    //  *      'field_a' => 20,
    //  *      'field_b' => 'my name'
    //  * ]
    //  */
    // public function insert(string $table, array $values)
    // {
    //     $value = $this->composeInsertInto($table, $values);

    //     $this->addQueryKeyword('insert_into', $value);
    // }

    // public function update(string $table)
    // {
    //     $this->addQueryKeyword('update', $table);
    // }

    // public function deleteFrom(string $table)
    // {
    //     $this->addQueryKeyword('delete_from', $table);
    // }

    // // Action extension
    // public function from(string $table)
    // {
    //     $this->prevKeywordIsMatched(['select']);

    //     $this->addQueryKeyword('from', $table, [
    //         'select'
    //     ], false);
    // }

    // public function set(array $values)
    // {
    //     $this->addQueryKeyword('set', $values, [
    //         'update'
    //     ]);
    // }

    // public function where(string $field, string $operator, $value)
    // {
    //     $statement = $this->composeOperatorStatement($field, $operator, $value);

    //     $this->prevKeywordIsMatched(['from', 'where']);

    //     $this->addQueryKeyword(
    //         'where', 
    //         $query, 
    //         [
    //             'update',
    //             'select',
    //             'delete_from'
    //         ], 
    //         false
    //     );
    // }

    // public function distinct()
    // {
    //     $this->addQueryKeyword('distinct', 'DISTINCT', [
    //         'select'
    //     ]);
    // }

    // public function limit(int $numRows)
    // {
    //     $this->addQueryKeyword('limit', $numRows, [
    //         'select'
    //     ]);
    // }

    // public function join(string $table)
    // {
    //     $join = $this->queryStructure['join'];

    //     \array_push($join, [
    //         $table => []
    //     ]);
        
    //     $this->addQueryKeyword('join', $join, [
    //         'select',
    //         'from'
    //     ]);
    // }

    // /**
    //  * 'join' => [
    //  *      'tableName' => [
    //  *          'x = y',
    //  *          'z = a'
    //  *      ]
    //  * ]
    //  */
    // public function on(string $field1, string $operator, string $field2)
    // {
    //     $statement = $this->composeOperatorStatement($field1, $operator, $field2);

    //     $this->prevKeywordIsMatched([
    //         'join',
    //         'on'
    //     ]);

    //     $join = $this->queryStructure['join'];
    //     $lastJoinedTable = \array_pop($join);
    //     // $lastJoinedTable = end($join);
    //     $lastJoinedTable[] = $statement;

    //     \array_push($join, $lastJoinedTable);

    //     $this->addQueryKeyword('join', $join, [
    //         'join'
    //     ]);
    // }

    // /**
    //  * [
    //  *      'field_a' => 'asc',
    //  *      'field_b' => 'desc'
    //  * ]
    //  */
    // public function orderBy(array $fields)
    // {
    //     $this->addQueryKeyword('order_by', $value, [
    //         'select'
    //     ]);
    // }

    // public function groupBy(array $fields)
    // {
    //     $this->addQueryKeyword('group_by', $fields, [
    //         'select'
    //     ]);
    // }
}