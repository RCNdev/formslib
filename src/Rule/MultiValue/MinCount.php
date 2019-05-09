<?php
namespace src\Rule\MultiValue;

use formslib\Rule\Rule;

class MinCount extends Rule
{
    public function evaluate(array $value)
    {
        return (count($value) >= $this->ruledfn) ? true : false;
    }
}
