<?php
namespace formslib\Field;

class KeyValuePair extends MultiPair
{
	public function &getObjectValue()
	{
		$this->_preProcessValues();

		$data = [];

		foreach ($this->indices as $i)
		{
			$data[$this->multi_values[$i.'__1']] = $this->multi_values[$i.'__2'];
		}

		return $data;
	}
}