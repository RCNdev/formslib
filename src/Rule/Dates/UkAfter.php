<?php
namespace formslib\Rule\Dates;

class UkAfter extends \formslib\Rule\Rule
{
	public function evaluate($value)
	{
		if (trim($value) == '') return true;

		$val = \Formslib::getUkDate($value);
		$check = \Formslib::getUkDate($this->ruledfn);

		return ($val >= $check);
	}
}