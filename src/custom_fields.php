<?php
class formslib_memberno extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('maxlength', 7, 'Membership number cannot be longer than 7 digits');
		$this->addRule('regex', '|^[0-9]{5,7}$|i', 'Membership number must only contain digits and be between 5 and 7 digits long');
	}
}

class formslib_nmcpin extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('regex', '/^[0-9]{2}[A-Z][0-9]{4}[A-Z]$/i', 'NMC PIN must be in the format 99A9999A (Letter at third and last position)');
	}
}
