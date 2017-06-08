<?php
namespace formslib\Field;

class MultiPair extends GenericMulti
{
	protected $indices = array();

	protected function _preProcessValues()
	{

	}

	public function __construct($name)
	{
		parent::__construct($name);

		$this->indices = array(1, 2, 4, 5, 6, 7, 9, 15);
	}

	protected function getSingleInstance($i)
	{
		$field = new Pair($this->name.'__'.$i);
		$field->forceOutputStyle($this->outputstyle);
		$field->addClass('form-control');
		// 			$field->value = $selected;

		return $field->getHTML() . CRLF;
	}

	public function getEmailValue()
	{
		$this->_preProcessValues();

		/*
		foreach ($this->indices as $i)
		{

		}
		*/
	}

	public function &getObjectValue()
	{
		$this->_preProcessValues();

		$data = null;

		/*
		foreach ($this->indices as $i)
		{

		}
		*/

		return $data;
	}
}