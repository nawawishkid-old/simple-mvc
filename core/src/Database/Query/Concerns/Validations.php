<?php

namespace Core\Database\Query\Concerns;

/**
 * 
 */
trait Validations
{
    /**
     * Validate given SQL verb.
     * 
     * @param string $verb SQL verb e.g. select, insert, update, delete.
     * @param array $previousVerbs Array of SQL verb methods which, one of them, has to be called to be valid.
     * 
     * @uses $this->verbIsAllowed
     * @uses $this->previousVerbIsMatched
     * 
     * @return bool Validation result.
     */
    private function verbIsValid(string $verb, array $previousVerbs)
    {
        return $this->verbIsAllowed($verb) && $this->previousVerbIsMatched($previousVerbs);
    }

    /**
     * Check whether the given verb is allowed to be called.
     * 
     * @param string $verb Verb to be verify.
     * 
     * @return bool Check result.
     */
    private function verbIsAllowed(string $verb)
    {
        return empty($this->state['possibleVerbs']) || in_array($verb, $this->state['possibleVerbs']);
    }

    /**
     * Check whether previously called verb method is match with the given array of verbs.
     * 
     * @param array $previousVerbs Array of SQL verb methods which, one of them, has to be called to be valid.
     * 
     * @return bool Check result.
     */
    private function previousVerbIsMatched(array $previousVerbs)
    {
        return empty($previousVerbs) || in_array($this->state['previousVerb'], $previousVerbs);
    }
    
    /**
     * Validate given SQL conjunction.
     * 
     * @param string $conjunction SQL conjunction e.g. where.
     * @param array $conjunctionOf Array of SQL conjunction methods which, one of them, has to be called to be valid.
     * 
     * @uses $this->conjunctionIsAllowed
     * @uses $this->previousVerbIsMatched
     * 
     * @return bool Validation result.
     */
    private function conjunctionIsValid($conjunction, $conjunctionOf)
    {
        return $this->conjunctionIsAllowed($conjunction) && $this->previousVerbIsMatched($conjunctionOf);
    }

    /**
     * Check whether the given conjunction is allowed to be called.
     * 
     * @param string $conjunction Conjunction to be verify.
     * 
     * @return bool Check result.
     */
    private function conjunctionIsAllowed($conjunction)
    {
        return empty($this->state['possibleConjunctions']) || in_array($conjunction, $this->state['possibleConjunctions']);
    }
    
    /**
     * Validate given SQL condition.
     * 
     * @param string $type Type of SQL condition e.g. and, or.
     * @param array $conditionOf Array of SQL condition methods which, one of them, has to be called to be valid.
     * 
     * @uses $this->conditionIsAllowed
     * @uses $this->previousConjunctionIsMatched
     * 
     * @return bool Validation result.
     */
    private function conditionIsValid($type, $conditionOf)
    {
        return $this->conditionIsAllowed($type) && $this->previousConjunctionIsMatched($conditionOf);
    }

    /**
     * Check whether the given condition is allowed to be called.
     * 
     * @param string $condition Condition to be verify.
     * 
     * @return bool Check result.
     */
    private function conditionIsAllowed($type)
    {
        return empty($this->state['possibleConditions']) || in_array($type, $this->state['possibleConditions']);
    }

    /**
     * Check whether previously called conjunction method is match with the given array of conjunctions.
     * 
     * @param array $previousVerbs Array of SQL conjunction methods which, one of them, has to be called to be valid.
     * 
     * @return bool Check result.
     */
    private function previousConjunctionIsMatched($conditionOf)
    {
        return $this->state['previousConjunction'] === $conditionOf;
    }

    /**
     * Modify given value to suit with  SQL statement.
     * 
     * @param mixed $value Value to be validate.
     * 
     * @return mixed Validated value.
     */
    private function validateValue($value)
    {
        $value = is_string($value) ? '"' . trim($value) . '"' : $value;
        
        return $value;
    }
}
