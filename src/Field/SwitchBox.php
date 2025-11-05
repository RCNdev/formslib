<?php
namespace formslib\Field;

class SwitchBox extends \formslib_checkbox
{
	public function __construct($name)
	{
		parent::__construct($name);

		$this->addAttr('role', 'switch');
		$this->addAttr('switch', null);
		$this->addGroupClass('form-switch');
		$this->setTickBefore();
	}
}