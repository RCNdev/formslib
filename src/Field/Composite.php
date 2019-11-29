<?php
namespace formslib\Field;

abstract class Composite extends Field
{
    protected $composites = [];
    public $composite_values = [];

    protected function _set_composites($composites)
    {
        $this->composites = $composites;

        // TODO: Review the need for this
        foreach ($this->composites as $key)
        {
            $this->composite_values[$key] = '';
        }
    }

    public function get_composites()
    {
        return $this->composites;
    }

    protected function _class_attr($extraclass = '')
    {
        $class_str = '';
        $classes = $this->classes;
        $classes[] = $extraclass;

        $class_str .= ' class="';
        $first = true;
        foreach ($classes as $classname)
        {
            if (! $first) $class_str .= ' ';
            $class_str .= $classname;
            $first = false;
        }
        $class_str .= '"';

        return $class_str;
    }

    public function getEmailValue()
    {
        return '[Composite field function getEmailValue() not overwritten]';
    }

    public function &getObjectValue()
    {
        throw new \Exception('Composite field function getObjectValue() not overwritten for field type '.get_class($this));
    }

    public function getJquerySelector()
    {
        return '[data-formslib-owner="fld_'.$this->name.'"] input';
    }

    public function checkMandatoryVars(array &$vars)
    {
        $missing = false;

        foreach ($this->composites as $key)
        {
            if (! isset($vars[$this->name . '__' . $key]) || trim((string)$vars[$this->name . '__' . $key]) === '') $missing = true;
        }

        return !$missing;
    }
}