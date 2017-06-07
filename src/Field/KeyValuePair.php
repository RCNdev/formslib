<?php
namespace formslib\Field;

class KeyValuePair extends Composite
{
	public function __construct($name)
	{
		parent::__construct($name);
		$this->_set_composites(array(
				'key',
				'value'
		));

		//TODO: Rule to make both sides mandatory?

// 		$this->addRule('composite_date_exists', '', 'The date entered was incomplete or does not exist');
	}

	public function getHTML()
	{
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
		$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr($classes) . ' name="' . $this->name . '__key" value="' . htmlspecialchars($this->composite_values['key']) . '" />';

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
		$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr($classes) . ' name="' . $this->name . '__value" value="' . htmlspecialchars($this->composite_values['value']) . '" />';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div><!-- /.col-xs-6 -->' . CRLF;
			$html .= '</div><!-- /.row -->' . CRLF;
		}

		return $html;
	}

	public function getEmailValue()
	{
		return $this->composite_values['year'] . '-' . sprintf('%02d', $this->composite_values['month']) . '-' . sprintf('%02d', $this->composite_values['day']);
	}

	public function &getObjectValue()
	{
		$date = null;

		if ($this->composite_values['year'] != 0 && $this->composite_values['month'] != 0 && $this->composite_values['day'] != 0)
		{
			$date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->getEmailValue().' 00:00:00');
		}

		return $date;
	}
}