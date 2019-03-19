<?php
namespace formslib\Field;

class Guid extends Text
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('maxlength', 38, 'GUIDs cannot be this long');
		$this->addRule('regex', '/^\{?[A-Z0-9]{8}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{4}-[A-Z0-9]{12}\}?$/i', 'Please make sure you enter a valid GUID with correctly-placed hyphens');
	}
}