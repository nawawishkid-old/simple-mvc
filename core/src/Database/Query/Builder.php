<?php

namespace Core\Database\Query;

class Builder
{
    use Concerns\VerbKeywords;
    use Concerns\ConjunctionKeywords;
    use Concerns\ConditionKeywords;
    use Concerns\Validations;

    private $rawQuery;

    private $preparedQuery;

    private $table;

    /**
     * Record all called verb
     * 
     * @see Builder::composeCompleteQuery()
     */
    private $calledVerbs = [];

    /**
     * Records state of the instance.
     */
    private $state = [
        'previousVerb' => null,
        'possibleVerbs' => [],
        'previousConjunctions' => null,
        'possibleConjunctions' => [],
        'possibleConditions' => []
    ];

    /**
     * Store all verb query informations
     */
    private $queryVerbs = [
        // Example structure
        // 'select' => [
        //     [
        //         'arguments' => ['tbl_a'],
        //         'conjunction' => [
        //             'where' => [
        //                 'first' => ['col_a', '=', 30],
        //                 'and' => [],
        //                 'or' => []
        //             ]
        //         ]
        //     ],
            // Other 'select' goes here
        // ]
    ];

    /**
     * Get states of the instance, and its table
     */
    public function __get($name)
    {
        if ($name === 'table') {
            return $this->table;
        }
        
        if (! in_array($name, array_keys($this->state))) {
            throw new \Exception(
                sprintf(
                    "Error: Given state, %s, not found. Available states are: %s",
                    $name,
                    implode(', ', $this->state)
                ), 
                1
            );
            
        }

        return $this->state[$name];
    }

    /**
     * Get query structure of the instance
     */
    public function getQueryVerbs($verb)
    {
        return $this->queryVerbs[$verb];
    }

    /**
     * Set database table to the instance. Must be called before other query method
     */
    public function table($tableName)
    {
        $this->table = $tableName;

        return $this;
    }

    // ============================== Query phrase addition methods ==========================
    /**
     * Add verb to query structure
     */
    private function addVerb($verb, $arguments, $nextVerbs = [], $prevVerbs = [], $possibleConjunctions = [])
    {
        // Validate verb
        if (! $this->verbIsValid($verb, $prevVerbs)) {
            throw new \Exception(
                sprintf(
                    "Error: Given verb, %s, is invalid. Possible verbs are: %s",
                    $verb,
                    implode(', ', $this->state['possibleVerbs'])
                ),
                1
            );
            
        }

        $this->state['possibleVerbs'] = $nextVerbs;
        $this->state['possibleConjunctions'] = $possibleConjunctions;
        $this->state['previousVerb'] = $verb;
        $this->calledVerbs[] = $verb;

        $structure = [
            'arguments' => $arguments,
            'conjunctions' => []
        ];
        
        if (empty($this->queryVerbs[$verb])) {
            $this->queryVerbs[$verb] = [];
        }

        $this->queryVerbs[$verb][] = $structure;
    }

    /**
     * Add conjunction to last called verb
     */
    private function addConjunction($conjunction, $arguments, $of = [], $possibleConditions = [])
    {
        if (! $this->conjunctionIsValid($conjunction, $of)) {
            throw new \Exception(
                sprintf(
                    "Error: Invalid conjunction of '%s'. Possible conjunctions are: %s",
                    $this->state['previousVerb'],
                    implode(', ', $this->possibleConjunctions)
                ), 
                1
            );
            
        }

        $this->state['possibleConditions'] = $possibleConditions;
        $this->state['previousConjunction'] = $conjunction;

        $lastVerbStructure = array_pop($this->queryVerbs[$this->state['previousVerb']]);
        
        if (empty($lastVerbStructure['conjunctions'][$conjunction])) {
            $lastVerbStructure['conjunctions'][$conjunction] = [];
        }

        $lastVerbStructure['conjunctions'][$conjunction]['first'] = $arguments;

        array_push($this->queryVerbs[$this->state['previousVerb']], $lastVerbStructure);
    }

    /**
     * Add condition to conjunction in last called verb
     */
    private function addCondition($type, $arguments, $of)
    {
        // Check previous verb
        // Check previous conjunction
        if (! $this->conditionIsValid($type, $of)) {
            throw new \Exception(
                sprintf(
                    "Error: Given condition, '%s', is an invalid condition of %s. Possible conditions are: %s",
                    $type,
                    $this->state['previousConjunction'],
                    implode(', ', $this->state['possibleConditions'])
                ),
                1
            );
            
        }

        $lastVerbStructure = array_pop($this->queryVerbs[$this->state['previousVerb']]);
        
        if (empty($lastVerbStructure['conjunctions'][$of][$type])) {
            $lastVerbStructure['conjunctions'][$of][$type] = [];
        }

        $lastVerbStructure['conjunctions'][$of][$type][] = $arguments;

        array_push($this->queryVerbs[$this->state['previousVerb']], $lastVerbStructure);
    }

    // ========================== Query composition methods ===============================
    /**
     * Compose ready to query statement
     */
    private function composeCompleteQuery(bool $prepared = false)
    {
        $composedQuery = [];

        foreach ($this->calledVerbs as $calledVerb) {
            $composedQuery[] = $this->composeAllVerbs(
                $calledVerb, 
                $this->queryVerbs[$calledVerb], 
                $prepared
            );
        }
        
        return implode(' ', $composedQuery);
    }

    private function composeAllVerbs($verbName, $verbs, bool $prepared = false)
    {
        $composedVerbs = [];

        foreach ($verbs as $verb) {
            // Compose statement
            // Return composed string
            $composeMethodName = 'compose' . ucfirst($verbName) . 'Verb';

            $result = call_user_func_array([$this, $composeMethodName], [$verb['arguments'], $prepared]);
            $result .= $this->composeAllConjunctions($verb, $prepared);

            $composedVerbs[] = $result;
        }
        
        return implode(' ', $composedVerbs);
    }

    private function composeAllConjunctions($verb, bool $prepared = false)
    {
        if (empty($verb['conjunctions'])) {
            return;
        }

        $composedConjunctions = [];

        foreach ($verb['conjunctions'] as $conjunction => $conditions) {
            $composeMethodName = 'compose' . ucfirst($conjunction) . 'Conjunction';

            $result = call_user_func_array([$this, $composeMethodName], [$conditions['first'], $prepared]);
            $result .= $this->composeAllConditions($conditions, $prepared);

            $composedConjunctions[] = $result;
        }
        
        return implode(' ', $composedConjunctions);
    }

    private function composeAllConditions($conditions, bool $prepared = false)
    {
        if (count($conditions) === 1) {
            return;
        }

        // Remove the condition of the conjunction.
        unset($conditions['first']);

        $composedConditions = [];

        // Iterates through all conditions of 'AND' or 'OR'
        foreach ($conditions as $condition => $subConditions) {
            // Condition of 'AND' or 'OR'
            foreach ($subConditions as $subCondition) {
                $composeMethodName = 'compose' . ucfirst($condition) . 'Condition';
                $composedConditions[] = call_user_func_array([$this, $composeMethodName], [$subCondition, $prepared]);
            }
        }
        
        return implode(' ', $composedConditions);
    }

    // ========================== Get query methods ==========================
    public function get()
    {
        if (empty($this->rawQuery)) {
            $this->rawQuery = $this->composeCompleteQuery();
        }

        return $this->rawQuery;
    }

    public function getPrepared()
    {
        if (empty($this->preparedQuery)) {
            $this->preparedQuery = $this->composeCompleteQuery(true);
        }

        return $this->preparedQuery;
    }

    // private function composeSelectStatement($data)
    // {
    //     $format = "SELECT %s FROM %s" . PHP_EOL;

    //     $selectInput = $data['input'][0];
    //     $fromInputs = $data['clauses']['from'];
    //     $whereInputs = $data['clauses']['where'];
    // }

    // private function composeWhereClause($data)
    // {
    //     $format = "WHERE %s %s %s" . PHP_EOL;

    //     $this->composeStatement($format, implode(' ', $data));
    // }

    // private function composeAndWhereClause($data)
    // {
    //     $format = "AND %s %s %s" . PHP_EOL;

    //     $this->composeStatement($format, implode(' ', $data));
    // }

    // private function composeOrWhereClause($data)
    // {
    //     $format = "OR %s %s %s" . PHP_EOL;

    //     $this->composeStatement($format, implode(' ', $data));
    // }

    // public function __construct()
    // {

    // }

    /**
     * Return prepared statement
     */
    // public function getPrepared()
    // {
    //     return $this->preparedStatement;
    // }

    // /**
    //  * Return statement
    //  */
    // public function get()
    // {
    //     return $this->rawStatement;
    // }

    /**
     * Reset instance
     */
    public function clear()
    {
        $this->statement = [];
        $this->rawStatement = null;
        $this->preparedStatement = null;
        $this->previousKeyword = null;
        $this->possibleKeywords = [];
    }

    // public function select($input)
    // {
    //     $data = [
    //         'input' => $input,
    //         'clauses' => [
    //             'from',
    //             'where'
    //         ]
    //     ];
    //     // $clause = "SELECT $input";

    //     $this->tryAppendingKeyword('select', $data);
    // }

    // public function insert(array $columnValues)
    // {
    //     $clause = "INSERT INTO";
    // }

    // public function into(string $tableName)
    // {
    //     $this->tryAppendingKeyword('into');
    // }

    // private function composeInsertStatement()
    // {
    //     $table = $this->getKeywordDetail('into')['input'];
    //     $columnValues = $this->getKeywordDetail('insert')['input'];

    //     $columns = implode(', ', array_keys($columnValues));
    //     $valuesArray = array_values($columnValues);
    //     $values = implode(', ', $valuesArray);
    //     $preparedValues = implode(', ', $this->prepareArrayValues($valuesArray));

    //     $format = "INSERT INTO %s (%s) VALUES (%s);";

    //     $rawStatement = $this->composeStatement($format, [
    //         $table,
    //         $columns,
    //         $values
    //     ]);

    //     $preparedStatement = $this->composeStatement($format, [
    //         $table,
    //         $columns,
    //         $preparedValues
    //     ]);
        
    //     $this->setRawStatement($rawStatement);
    //     $this->setPreparedStatement($preparedStatment);
    // }

    // private function getKeywordDetail($keyword)
    // {
    //     return $this->statement[$keyword];
    // }

    // private function setRawStatement(string $composedStatement)
    // {
    //     $this->rawStatement = $composedStatement;
    // }

    // private function setPreparedStatement(string $composedStatement)
    // {
    //     $this->preparedStatement = $composedStatement;
    // }

    // private function composeStatement(string $format, $inputs)
    // {
    //     return sprintf($format, $inputs);
    // }

    // private function prepareArrayValues(array $toBePrepared)
    // {
    //     return array_fill(0, count($toBePrepared), '?');
    // }

    /**
     * 
     * 
     * @param string $keyword
     * @param array $previousKeywords Keywords to check if $this->previousKeyword is matched one of them.
     * @param array $nextKeywords Keywords which will be used to set the next possible keywords.
     */
    // private function tryAppendingKeyword(
    //     string $keyword, 
    //     // string $clause, 
    //     $input = null, 
    //     array $previousKeywords = null, 
    //     array $nextKeywords = null
    // )
    // {
    //     if (! $this->keywordIsValid($previousKeywords)) {
    //         throw new \Exception("Error: Given keyword is not allowed, $keyword", 1);
            
    //     }

    //     $this->appendKeyword($keyword, $clause, $input);
    //     $this->setPreviousKeyword($keyword);
    //     $this->setPossibleKeywords($nextKeywords);
    // }

    // /**
    //  * 
    //  */
    // private function appendKeyword(string $keyword, $input = null)
    // {
    //     $this->statement[] = [
    //         'keyword' => $keyword,
    //         // 'clause' => $clause,
    //         'input' => $input
    //     ];
    // }

    // /**
    //  * 
    //  */
    // private function keywordIsValid($keyword, $previousKeywords)
    // {
    //     return $this->keywordIsPossible($keyword) && $this->previousKeywordIsMatched($previousKeywords);
    // }

    // /**
    //  * 
    //  */
    // private function keywordIsPossible(string $keyword)
    // {
    //     return empty($this->possibleKeywords) || in_array($keyword, $this->possibleKeywords);
    // }

    // /**
    //  * 
    //  */
    // private function previousKeywordIsMatched($previousKeywords)
    // {

    //     return is_null($previousKeywords) || in_array($this->previousKeyword, $previousKeywords);
    // }

    // /**
    //  * 
    //  */
    // private function setPreviousKeyword($keyword)
    // {
    //     $this->previousKeyword = $keyword;
    // }

    // /**
    //  * 
    //  */
    // private function setPossibleKeywords($keywords)
    // {
    //     $keywords = is_array($keywords) ? $keywords : func_get_args();

    //     $this->possibleKeywords = $keywords;
    // }
}