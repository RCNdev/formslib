<?php
namespace formslib\Field;

abstract class AutocompleteLookup extends Text
{
	protected $minChars = 3;

	public function setMinChars($charCount)
	{
		$this->minChars = $charCount;
	}
}