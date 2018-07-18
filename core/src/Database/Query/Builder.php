<?php

namespace Core\Database\Query;

class Builder
{
    use Concerns\VerbKeywords;
    use Concerns\ConjunctionKeywords;
    use Concerns\ConditionKeywords;
    use Concerns\Validations;

    /**
     * @property string Raw SQL query (unprepared).
     */
    private $rawQuery;

    /**
     * @property string Prepared SQL query.
     */
    private $preparedQuery;

    /**
     * @property string Database table name of the query.
     */
    private $table;

    /**
     * @property array Record all called verb method name.
     * 
     * @see Builder::composeCompleteQuery()
     */
    private $calledVerbs = [];

    /**
     * @property array State of the instance.
     */
    private $state = [
        'previousVerb' => null,
        'possibleVerbs' => [],
        'previousConjunctions' => null,
        'possibleConjunctions' => [],
        'possibleConditions' => []
    ];

    /**
     * @property array Store all verb query information including its arguments and conjunction.
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
     * Get states of the instance, and its table.
     * 
     * @api
     * @param string $name Name of the state.
     * 
     * @return mixed $this->state[$name]
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
     * Get information of query verbs of the instance.
     * 
     * @api
     * @param string $verb Verb name e.g. select, insert, update, delete.
     * 
     * @return array Query verb information structure array.
     */
    public function getQueryVerbs($verb)
    {
        return $this->queryVerbs[$verb];
    }

    /**
     * Set database table to the instance. Must be called before other query method.
     * 
     * @api
     * @param string $tableName Name of the database table to includes in query.
     * 
     * @return $this
     */
    public function table($tableName)
    {
        $this->table = $tableName;

        return $this;
    }

    // ============================== Query phrase addition methods ==========================
    /**
     * Add verb to query verbs ($this->queryVerbs)
     * 
     * @param string $verb Query verb
     * @param mixed $arguments Arguments of the verb, depends on what verb it is.
     * @param array $nextVerbs Verb methods that are allowed to call next.
     * @param array $prevVerbs Verb methods that must be called before this verb method.
     * @param array $possibleConjunctions Query conjunction methods that are allowed for this verb method.
     * 
     * @return void
     */
    private function addVerb($verb, $arguments, $nextVerbs = [], $prevVerbs = [], $possibleConjunctions = [])
    {
        // Validate verb.
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

        // Set builder' state.
        $this->state['possibleVerbs'] = $nextVerbs;
        $this->state['possibleConjunctions'] = $possibleConjunctions;
        $this->state['previousVerb'] = $verb;
        $this->calledVerbs[] = $verb;

        // Define verb information structure.
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
     * Add conjunction to last called verb.
     * 
     * @param string $conjunction Query conjunction method name.
     * @param mixed $arguments Arguments of the conjunction.
     * @param array $of Array of verbs which this conjunction belongs to.
     * @param array $possibleConditions Query conditions that are allowed for this conjunction.
     * 
     * @return void
     */
    private function addConjunction($conjunction, $arguments, $of = [], $possibleConditions = [])
    {
        // Validtaion.
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

        // Update builder' state.
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
     * Add condition to conjunction in last called verb.
     * 
     * @param string $type Type of condition i.e. and, or.
     * @param mixed $arguments Arguments of the condition.
     * @param string $of Name of conjunction method which this condition belongs to.
     * 
     * @return void
     */
    private function addCondition($type, $arguments, string $of)
    {
        // Validation.
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
     * Compose ready to query statement.
     * 
     * @param bool $prepared Whether to prepare the query or not.
     * 
     * @return string Composed query.
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

    /**
     * Compose all registered verbs.
     * 
     * @param string $verbName Name of the verb method.
     * @param array $verbs Array of info structure of the verb.
     * @param bool $prepared Whether to prepare the query or not.
     * 
     * @return string Composed verbs.
     */
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

    /**
     * Compose all registered conjunctions of the given verb.
     * 
     * @param array $verb Verb information array.
     * @param bool $prepared Whether to prepare the query or not.
     * 
     * @return string Composed conjunction.
     */
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

    /**
     * Compose all registered conditions of the given conjunction.
     * 
     * @param array $conditions Array of conditions.
     * @param bool $prepared Whether to prepare the query or not.
     * 
     * @return string Composed conjunction.
     */
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
    /**
     * Return SQL query without preparation.
     * 
     * @return string $this->rawQuery SQL query.
     */
    public function get()
    {
        if (empty($this->rawQuery)) {
            $this->rawQuery = $this->composeCompleteQuery();
        }

        return $this->rawQuery;
    }
    /**
     * Return prepared SQL query.
     * 
     * @return string $this->preparedQuery SQL query.
     */
    public function getPrepared()
    {
        if (empty($this->preparedQuery)) {
            $this->preparedQuery = $this->composeCompleteQuery(true);
        }

        return $this->preparedQuery;
    }

    /**
     * Reset instance
     * 
     * @return void
     */
    public function clear()
    {
        $this->rawQuery = null;
        $this->preparedQuery = null;
        $this->queryVerbs = [];
        $this->calledVerbs = [];
        $this->state = [
            'previousVerb' => null,
            'possibleVerbs' => [],
            'previousConjunctions' => null,
            'possibleConjunctions' => [],
            'possibleConditions' => []
        ];
    }
}