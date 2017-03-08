<?php
namespace formslib\Rule\Composite;

use formslib\Rule\Rule;

class MonthYearMin extends Rule
{
	public function evaluate($value)
	{
		if ($value['year'] < $this->ruledfn['year']) return false;
		if ($value['year'] > $this->ruledfn['year']) return true;

		// Current year

		if ($value['month'] < $this->ruledfn['month']) return false;
		if ($value['month'] >= $this->ruledfn['month']) return true;
	}
}