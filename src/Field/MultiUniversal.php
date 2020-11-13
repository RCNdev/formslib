<?php
namespace formslib\Field;

class MultiUniversal extends GenericMulti
{
	protected $indices = [];
	private $preprocessed = false;
	protected $separator = ';';
	protected $field_class;
	protected $call_each;

	public function __construct($name)
	{
		parent::__construct($name);

		$this->indices = [];
	}

	public function &setFieldClass($class_name)
	{
		if (!class_exists($class_name))
		{
			throw new \OutOfBoundsException('Class "'.$class_name.'" did not exist');
		}

		$this->field_class = $class_name;

		return $this;
	}

	public function &setCallEach(callable $function)
	{
		$this->call_each = $function;

		return $this;
	}

	protected function getSingleInstance($i, $setValue = false)
	{
		$field = new $this->field_class($this->name.'__'.$i);
		$field->forceOutputStyle($this->outputstyle)
		->addClass('form-control')
		->addAttr('data-index', $i);

		$call = $this->call_each;

		$call($field);

		if ($setValue) $field->value = $this->multi_values[$i];

		return $field->getHTML() . CRLF;
	}

	protected function _preProcessValues()
	{
		if (!$this->preprocessed)
		{
			$indices_str = isset($this->multi_values['control']) ? trim($this->multi_values['control']) : '';
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