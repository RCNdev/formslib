<?php
namespace formslib\Field;

class MultiPair extends GenericMulti
{
	private $preprocessed = false;

	protected function _preProcessValues()
	{
		if (!$this->preprocessed)
		{
			$indices_str = trim($this->multi_values['control']);
			$indices = explode(',', $indices_str);

			foreach ($indices as $i)
			{
				if (preg_match('/^[0-9]+$/', trim($i)))
				{
					$this->indices[] = trim($i);
				}
			}

			$this->preprocessed = true;
		}
	}

	public function __construct($name)
	{
		parent::__construct($name);

		$this->indices = [];
	}

	protected function getSingleInstance($i, $setValue = false)
	{
		$field = new Pair($this->name.'__'.$i);
		$field->forceOutputStyle($this->outputstyle);
		$field->addClass('form-control');
		$field->addAttr('data-index', $i);

		if ($setValue) $field->composite_values = array(1 => $this->multi_values[$i.'__1'], 2 => $this->multi_values[$i.'__2']);

		return $field->getHTML() . CRLF;
	}

	/*
	public function getEmailValue()
	{
		$this->_preProcessValues();

		// TODO: Implement email values
	}
	*/

	public function &getObjectValue()
	{
		$this->_preProcessValues();

		$data = null;

		foreach ($this->indices as $i)
		{
			$data[$i] = array($this->multi_values[$i.'__1'], $this->multi_values[$i.'__2']);
		}

		return $data;
	}
}