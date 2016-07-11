<?php
namespace formslib\Field;

abstract class MultiValue extends Field
{
	public $multi_values = array();
	public $composite_values = null;
	protected $maxvalue;

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
		return '[MultiValue field function getEmailValue() not overwritten]';
	}

	public function getNextIndex()
	{
		$this->maxvalue++;
		return $this->maxvalue;
	}

	public function &setClientSideData($clientside = true)
	{
		$this->clientside = $clientside;

		return $this;
	}

	public function getJs()
	{
		$js = parent::getJs();

		return $js;
	}
}