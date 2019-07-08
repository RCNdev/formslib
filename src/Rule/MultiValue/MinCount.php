<?php
namespace formslib\Rule\MultiValue;

use formslib\Rule\Rule;

class MinCount extends Rule
{
    /**
     * 
     * {@inheritDoc}
     * @see \formslib_rule::evaluate()
     * @param array $value
     */
    public function evaluate($value)
    {
        return (count($value) >= $this->ruledfn) ? true : false;
    }
}
