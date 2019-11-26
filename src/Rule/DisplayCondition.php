<?php
namespace formslib\Rule;

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
}