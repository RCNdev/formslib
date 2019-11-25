<?php
namespace formslib\Field;

/**
 *
 * @author sheppardp
 * @todo Review autocomplete options
 * @link https://raw.githubusercontent.com/uohzxela/fuzzy-autocomplete/master/fuzzy-autocomplete.js
 */
abstract class AutocompleteLookup extends Text
{
	protected $minChars = 3;

	public function &setMinChars($charCount)
	{
		$this->minChars = $charCount;

		return $this;
	}
}