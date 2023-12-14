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

        if ($operator == Operator::IN && !is_array($value))
        {
        	throw new \Exception('When setting an IN display condition, value must be an array');
        }
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

            case Operator::NE:
            	return (!isset($vars[$this->field_name]) || $vars[$this->field_name] != $this->value);

            case Operator::IN:
                return (isset($vars[$this->field_name]) && in_array($vars[$this->field_name], $this->value));

            case Operator::CHECKED:
                return (isset($vars[$this->field_name]));

            case Operator::PRESENT:
                return (isset($vars[$this->field_name.'__'.$this->value]));

            case Operator::ANY:
            	$length = strlen($this->field_name) + 2;
            	foreach (array_keys($vars) as $name)
            	{
            		if (substr($name, 0, $length) == $this->field_name.'__')
            		{
            			return true;
            		}
            	}
            	return false;

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


            case Operator::NE:
            	return ($field->value != $this->value);


            case Operator::IN:
                return in_array($field->value, $this->value);


            case Operator::CHECKED:
            	/** @var \formslib_checkbox $field */
                return ($field->isChecked() == $this->value);


            case Operator::PRESENT:
                /** @var \formslib\Field\Composite $field */
                foreach ($field->composite_values as $key => $value)
                {
                    if ($key == $this->value && $value != '')
                    {
                    	return true;
                    }
                }
                return false;


            case Operator::ANY:
            	/** @var \formslib\Field\Composite $field */
            	return (count($field->composite_values));


            default:
                throw new \Exception('Invalid operator');
        }
    }
}