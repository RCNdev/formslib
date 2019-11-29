<?php
namespace formslib\Rule;

use formslib\Operator;

class DisplayCondition
{
    private $field_name;
    private $operator;
    private $value;

    public function __construct($fieldName, $operator, $value)
    {
        $this->field_name = $fieldName;
        $this->operator = $operator;
        $this->value = $value;
    }

    public function getFieldName()
    {
        return $this->field_name;
    }

    public function getOperator()
    {
        return $this->operator;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * Evaluate this display condition against the given variables
     *
     * @param array $vars
     * @return boolean
     *
     * @todo Implement display conditions
     */
    public function evaluateVars(array &$vars)
    {
        switch ($this->operator)
        {
            case Operator::EQ:
                return (isset($vars[$this->field_name]) && $vars[$this->field_name] == $this->value);
                break;


            case Operator::IN:
                return true;
                break;


            case Operator::CHECKED:
                return true;
                break;


            case Operator::PRESENT:
                return true;
                break;

            default:
                throw new \Exception('Invalid operator');

        }
    }
}