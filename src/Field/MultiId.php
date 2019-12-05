<?php
namespace formslib\Field;

class MultiId extends GenericMulti
{
	protected $indices = [];
	private $preprocessed = false;
	protected $separator = ';';

	public function __construct($name)
	{
		parent::__construct($name);

		$this->indices = [];
	}

	protected function getSingleInstance($i, $setValue = false)
	{
		$field = new \formslib_integer($this->name.'__'.$i);
		$field->forceOutputStyle($this->outputstyle)
		->addClass('form-control')
		->addAttr('data-index', $i);

		if ($setValue) $field->value = $this->multi_values[$i];

		return $field->getHTML() . CRLF;
	}

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

	public function getEmailValue()
	{
		return implode($this->separator, $this->getObjectValue());
	}

	public function &getObjectValue()
	{
		$vals = $this->multi_values;

		unset($vals['control']);

		return $vals;
	}

	public function &setSeparator($separator)
	{
		$this->separator = $separator;

		return $this;
	}
}