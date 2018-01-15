<?php
namespace formslib\Rule\Dates;

class UkAfter extends \formslib\Rule\Rule
{
	public function evaluate($value)
	{
		if (trim($value) == '') return true;

		$check = \Formslib::getUkDate($this->ruledfn);

		try
		{
			$val = \Formslib::getUkDate($value);
		}
		catch (\Exception $e)
		{
			return false;
		}

		return ($val >= $check);
	}
}