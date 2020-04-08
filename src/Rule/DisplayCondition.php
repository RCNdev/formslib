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
     */
    public function evaluateVars(array &$vars)
    {
        switch ($this->operator)
        {
            case Operator::EQ:
                return (isset($vars[$this->field_name]) && $vars[$this->field_name] == $this->value);
                break;


            case Operator::IN:
                //TODO: Implement IN operator
                return true;
                break;


            case Operator::CHECKED:
                return (isset($vars[$this->field_name]));
                break;


            case Operator::PRESENT:
                return (isset($vars[$this->field_name.'__'.$this->value]) == $this->value);
                break;

            default:
                throw new \Exception('Invalid operator');
        }
    }

    public function evaluateField(\formslib\Field\Field &$field)
    {
        switch ($this->operator)
        {
            case Operator::EQ:
                return ($field->value == $this->value);
                break;


            case Operator::IN:
                //TODO: Implement IN operator
                return true;
                break;


            case Operator::CHECKED:
                return (($field->value == $field->checkedvalue) == $this->value);
                break;


            case Operator::PRESENT:
                /** @var \formslib\Field\Composite $field */
                foreach ($field->composite_values as $key => $value)
                {
                    if ($key == $this->value && $value != '') return true;
                }

                return false;
                break;

            default:
                throw new \Exception('Invalid operator');
        }
    }
}