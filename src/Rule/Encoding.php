<?php
namespace formslib\Rule;

class Encoding extends Rule
{
    public function evaluate($value)
    {
        return mb_check_encoding($value ?? '', $this->ruledfn);
    }
}