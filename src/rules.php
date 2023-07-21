<?php

abstract class formslib_rule
{

	protected $ruledfn, $errormessage, $field;

	public function __construct($ruledfn, $errormessage, &$field)
	{
		$this->ruledfn = $ruledfn;
		$this->errormessage = $errormessage;
		$this->field = &$field;
	}

	public function evaluate($value)
	{
		return true;
	}

	public function getError()
	{
		return $this->errormessage;
	}

	public function get_jquery_condition()
	{
		return 'if (!true) {';
	}
}

class formslib_rule_regex extends formslib_rule
{

	public function evaluate($value)
	{
		if ($value == '')
		{
			return true;
		}
		elseif (preg_match($this->ruledfn, trim($value)))
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	public function get_jquery_condition()
	{
		$regex = $this->ruledfn;
		$delimiter = substr($regex, 0, 1);

		if ($delimiter !== '/')
		{
			$endpos = strrpos($regex, $delimiter);
			$jsregex = '/' . str_replace('/', '\/', substr($regex, 1, $endpos - 1)) . '/' . substr($regex, $endpos + 1);
		}
		else
		{
			$jsregex = $regex;
		}

		return 'if (!val.match(' . $jsregex . ')) {';
	}
}

class formslib_rule_maxlength extends formslib_rule
{

	public function __construct($ruledfn, $errormessage, &$field)
	{
		parent::__construct($ruledfn, $errormessage, $field);
		$field->addAttr('maxlength', $ruledfn);
	}

	public function evaluate($value)
	{
		return (strlen($value) <= $this->ruledfn) ? true : false;
	}

	public function get_jquery_condition()
	{
		return 'if (!(val.length<=' . $this->ruledfn . ')) {';
	}
}

class formslib_rule_maxwords extends formslib_rule
{

	public function evaluate($value)
	{
		$value = str_replace("\xE2\x80\x99", "'", $value); // Replace UTF-8 rsquo with standard apostrophe

		$count = str_word_count($value, 0);

		return ($count <= $this->ruledfn) ? true : false;
	}
}

class formslib_rule_minval extends formslib_rule
{

	/**
	 *
	 * @param mixed $ruledfn
	 * @param string $errormessage
	 * @param \formslib\Field\Field $field
	 */
	public function __construct($ruledfn, $errormessage, &$field)
	{
		parent::__construct($ruledfn, $errormessage, $field);

		if (is_a($field, \formslib_number::class))
		{
			$field->addAttr('min', $ruledfn);
		}
	}

	public function evaluate($value)
	{
		return ($value >= $this->ruledfn) ? true : false;
	}

	public function get_jquery_condition()
	{
		return 'if (!(val>=' . $this->ruledfn . ')) {';
	}
}

class formslib_rule_maxval extends formslib_rule
{

	/**
	 *
	 * @param mixed $ruledfn
	 * @param string $errormessage
	 * @param \formslib\Field\Field $field
	 */
	public function __construct($ruledfn, $errormessage, &$field)
	{
		parent::__construct($ruledfn, $errormessage, $field); // Parent constructor

		if (is_a($field, \formslib_number::class))
		{
			$field->addAttr('max', $ruledfn);
		}
	}

	public function evaluate($value)
	{
		return ($value <= $this->ruledfn) ? true : false;
	}

	public function get_jquery_condition()
	{
		return 'if (!(val<=' . $this->ruledfn . ')) {';
	}
}

class formslib_rule_sqldate extends formslib_rule
{

	public function evaluate($value)
	{
		// Allow blank values - mandatory validation is handled elsewhere
		if (trim($value) == '') return true;

		// Check for correct SQL format
		$matches = [];
		if (!preg_match('|^([0-9]{4})-([0-9]{2})-([0-9]{2})$|i', $value, $matches)) return false;

		return checkdate($matches[2], $matches[3], $matches[1]);
	}
}

class formslib_rule_positive extends formslib_rule
{

	/**
	 *
	 * @param mixed $ruledfn
	 * @param string $errormessage
	 * @param \formslib\Field\Field $field
	 */
	public function __construct($ruledfn, $errormessage, &$field)
	{
		parent::__construct($ruledfn, $errormessage, $field);

		if (is_a($field, \formslib_number::class))
		{
			$field->addAttr('min', 0);
			$field->addAttr('inputmode', 'decimal');
		}
	}

	public function evaluate($value)
	{
		if ($value < 0)
			return false;
		else
			return true;
	}

	public function get_jquery_condition()
	{
		return 'if (val<0){';
	}
}

class formslib_rule_composite_date_exists extends formslib_rule
{

	public function evaluate($value)
	{
		if ($value['day'] == '' && $value['month'] == '' && $value['year'] == '') return true;

		if ($value['day'] == '0' && $value['month'] == '0' && $value['year'] == '0') return true;

		return checkdate($value['month'], $value['day'], $value['year']);
	}
}

class formslib_rule_composite_date_min extends formslib_rule
{

	public function evaluate($value)
	{
		if ($value['year'] < $this->ruledfn['year']) return false;

		if ($value['year'] > $this->ruledfn['year']) return true;

		// Current year

		if ($value['month'] < $this->ruledfn['month']) return false;

		if ($value['month'] > $this->ruledfn['month']) return true;

		// Current month

		if ($value['day'] >= $this->ruledfn['day']) return true;

		return false;
	}
}

class formslib_rule_composite_date_max extends formslib_rule
{

	public function evaluate($value)
	{
		if ($value['year'] > $this->ruledfn['year']) return false;

		if ($value['year'] < $this->ruledfn['year']) return true;

		// Current year

		if ($value['month'] > $this->ruledfn['month']) return false;

		if ($value['month'] < $this->ruledfn['month']) return true;

		// Current month

		if ($value['day'] <= $this->ruledfn['day']) return true;

		return false;
	}
}

class formslib_rule_compsite_sortcode extends formslib_rule
{

	public function evaluate($value)
	{
		$cs = [
			'1',
			'2',
			'3'
		];
		foreach ($cs as $c)
		{
			if (!preg_match('|^[0-9]{2}$|i', $value[$c])) return false;
		}
		return true;
	}
}

class formslib_rule_minlength extends formslib_rule
{

	public function __construct($ruledfn, $errormessage, &$field)
	{
		parent::__construct($ruledfn, $errormessage, $field);
	}

	public function evaluate($value)
	{
		return (strlen($value) >= $this->ruledfn) ? true : false;
	}

	public function get_jquery_condition()
	{
		return 'if (!(val.length>=' . $this->ruledfn . ')) {';
	}
}

class formslib_rule_composite_minselections extends formslib_rule
{

	public function evaluate($value)
	{
		$count = 0;
		foreach ($value as $val)
		{
			if (!is_null($val) && $val != '') $count++;
		}

		return ($count >= $this->ruledfn) ? true : false;
	}
}

class formslib_rule_composite_maxselections extends formslib_rule
{

	public function evaluate($value)
	{
		$count = 0;
		foreach ($value as $val)
		{
			if (!is_null($val) && $val != '') $count++;
		}

		return ($this->ruledfn >= $count) ? true : false;
	}
}

class formslib_rule_date_format extends formslib_rule
{

	public function __construct($ruledfn, $errormessage, &$field)
	{
		parent::__construct($ruledfn, $errormessage, $field); // Parent constructor

		$field->addAttr('maxlength', 10);
	}

	public function evaluate($value)
	{
		if ($value == '')
		{
			return true;
		}

		switch ($this->ruledfn)
		{
			case 'uk':
				return (preg_match('|^[0-9]{2}/[0-9]{2}/[0-9]{4}$|', trim($value)));
				break;

			default:
				throw new Exception('Unknown date format');
		}
	}

	public function get_jquery_condition()
	{
		//TODO: Return some regex
		return 'if (!true) {';
	}
}

class formslib_rule_date_exists extends formslib_rule
{

	public function evaluate($value)
	{
		if (trim($value) == '') return true;

		switch ($this->ruledfn)
		{
			case 'uk':
				$bits = explode('/', $value);

				$day = $bits[0];
				$month = $bits[1];
				$year = $bits[2];
				break;

			default:
				throw new Exception('Unknown date format validation type');
		}

		return checkdate($month, $day, $year);
	}
}

class formslib_rule_composite_timerangeformat extends formslib_rule
{

	public function evaluate($value)
	{
		$cs = [
			'start',
			'end',
			'time'
		];
		foreach ($cs as $c)
		{
			if (isset($value[$c]) && $value[$c] != '' && !preg_match('/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/', $value[$c]))
			{
				return false;
			}
		}
		return true;
	}
}

class formslib_rule_composite_timerangeorder extends formslib_rule
{

	public function evaluate($value)
	{
		$start = (int)str_replace(':', '', $value['start']);
		$end = (int)str_replace(':', '', $value['end']);

		if ($end < $start) return false;

		return true;
	}
}
