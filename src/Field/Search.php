<?php
namespace formslib\Field;

class Search extends Text
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->inputType = 'search';
		$this->addAttr('inputmode', 'search');
	}
}