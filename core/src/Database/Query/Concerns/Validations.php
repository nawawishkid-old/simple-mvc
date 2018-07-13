<?php

namespace Core\Database\Query\Concerns;

/**
 * 
 */
trait Validations
{
    
    /**
     * 
     */
    private function verbIsValid($verb, $previousVerbs)
    {
        return $this->verbIsPossible($verb) && $this->previousVerbIsMatched($previousVerbs);
    }

    /**
     * 
     */
    private function verbIsPossible(string $verb)
    {
        return empty($this->state['possibleVerbs']) || in_array($verb, $this->state['possibleVerbs']);
    }

    /**
     * 
     */
    private function previousVerbIsMatched($previousVerbs)
    {
        return empty($previousVerbs) || in_array($this->state['previousVerb'], $previousVerbs);
    }
    
    /**
     * 
     */
    private function conjunctionIsValid($type, $conjunctionOf)
    {
        return $this->conjunctionIsPossible($type) && $this->previousVerbIsMatched($conjunctionOf);
    }

    /**
     * 
     */
    private function conjunctionIsPossible($type)
    {
        return empty($this->state['possibleConjunctions']) || in_array($type, $this->state['possibleConjunctions']);
    }
    
    /**
     * 
     */
    private function conditionIsValid($type, $conditionOf)
    {
        return $this->conditionIsPossible($type) && $this->previousConjunctionIsMatched($conditionOf);
    }

    /**
     * 
     */
    private function conditionIsPossible($type)
    {
        return empty($this->state['possibleConditions']) || in_array($type, $this->state['possibleConditions']);
    }

    /**
     * 
     */
    private function previousConjunctionIsMatched($conditionOf)
    {
        return $this->state['previousConjunction'] === $conditionOf;
        // return in_array($this->state['previousConjunction'], $conditionOf);
    }

    /**
     * 
     */
    private function validateValue($value)
    {
        $value = is_string($value) ? '"' . trim($value) . '"' : $value;
        
        return $value;
    }
}
