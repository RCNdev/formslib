<?php
namespace formslib\Field;

use formslib\Utility\Security;

class Pair extends Composite
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->_set_composites([
			'1',
			'2'
		]);

		//TODO: Rule to make both sides mandatory?

		// 		$this->addRule('composite_date_exists', '', 'The date entered was incomplete or does not exist');
	}

	public function getHTML()
	{
		//TODO: Switch for textareas

		$html = '';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '<div class="row">' . CRLF;
			$html .= '<div class="col-xs-6">' . CRLF;
			$classes = '';
		}
		else
		{
			$classes = ' input-mini';
		}

		// Key
		$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr($classes) . ' name="' . $this->name . '__1" value="' . Security::escapeHtml($this->composite_values[1]) . '" />';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-xs-6">' . CRLF;
			$classes = '';
		}
		else
		{
			$classes = ' input-small col-sm-6';
		}

		// Value
		$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr($classes) . ' name="' . $this->name . '__2" value="' . Security::escapeHtml($this->composite_values[2]) . '" />';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div><!-- /.col-xs-6 -->' . CRLF;
			$html .= '</div><!-- /.row -->' . CRLF;
		}

		return $html;
	}

	public function getEmailValue()
	{
		return $this->composite_values[1] . ': ' . $this->composite_values[2];
	}

	public function &getObjectValue()
	{
		$array = array($this->composite_values[1], $this->composite_values[2]);

		return $array;
	}
}