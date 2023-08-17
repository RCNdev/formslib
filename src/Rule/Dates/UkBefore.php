<?php
namespace formslib\Rule\Dates;

class UkBefore extends \formslib\Rule\Rule
{
	public function evaluate($value)
	{
		if (is_null($value) || trim($value) == '')
		{
			return true;
		}

		$check = \Formslib::getUkDate($this->ruledfn);

		try
		{
			$val = \Formslib::getUkDate($value);
		}
		catch (\Exception $e)
		{
			return false;
		}

		return ($val <= $check);
	}
}