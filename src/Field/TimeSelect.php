<?php
namespace formslib\Field;

class TimeSelect extends Composite
{
    /** @var \formslib_select */
    protected $field_hour;

    /** @var \formslib_select */
    protected $field_minute;

    public function __construct($name)
    {
        parent::__construct($name);
        $this->_set_composites([
            'hour',
            'minute'
        ]);

        $this->field_hour = new \formslib_select($name.'__hour');
        $this->field_minute = new \formslib_select($name.'__minute');
    }

    /**
     *
     * @param string $startTime
     * @param string $endTime
     * @throws \Exception
     * @return \formslib\Field\DateSelect
     */
    public function &setTimeRange($startTime, $endTime, $minuteInterval = 15, $minuteOffset = 0)
	{
		$patternTime = '/^[0-2][0-9]:[0-5][0-9]$/';
		if (!preg_match($patternTime, $startTime) || !preg_match($patternTime, $endTime)) throw new \Exception('Invalid time format');

		if ($startTime > $endTime) throw new \Exception('End time constraint before start date');

        $startHour = (int)substr($startTime, 0, 2);
        $endHour = (int)substr($endTime, 0, 2);

        $hours = [];
        for ($h = $startHour; $h <= $endHour; $h++)
        {
            $hours[$h] = sprintf('%02s', $h);
        }

        $minutes = [];
        for ($m = $minuteOffset; $m < 60; $m += $minuteInterval)
        {
            $minutes[$m] = sprintf('%02s', $m);
        }

		$this->field_hour->setOptions(['' => '-'] + $hours);
		$this->field_minute->setOptions(['' => '-'] + $minutes);

		return $this;
	}

	public function getHTML()
	{
	    $html = '';

	    if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
	    {
	        $html .= '<div class="row">' . CRLF;
	        $html .= '<div class="col-xs-4">' . CRLF;
	        $classes = '';
	    }
	    else
	    {
	        $classes = ' input-mini';
	    }

	    $this->field_hour->addClasses($this->getClasses());
	    $html .= $this->field_hour->getHTML();

// 	    // Day
// 	    $html .= '<select' . $this->_custom_attr() . $this->_class_attr('formslib_date__day'.$classes) . ' name="' . $this->name . '__day">' . CRLF;
// 	    $html .= '<option value="0">Day</option>';
// 	    for ($i = 1; $i <= 31; $i++)
// 	    {
// 	        $html .= '<option value="' . $i . '"';
// 	        if ($this->composite_values['day'] == $i) $html .= ' selected="selected"';
// 	        $html .= '>' . $i . '</option>' . CRLF;
// 	    }
// 	    $html .= '</select>' . CRLF;

	    if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
	    {
	        $html .= '</div>' . CRLF;
	        $html .= '<div class="col-xs-4">' . CRLF;
	        $classes = '';
	    }
	    else
	    {
	        $classes = ' input-small col-sm-6';
	    }

	    $this->field_minute->addClasses($this->getClasses());
	    $html .= $this->field_minute->getHTML();

// 	    // Month
// 	    $html .= '<select' . $this->_custom_attr() . $this->_class_attr('formslib_date__month'.$classes) . ' name="' . $this->name . '__month">' . CRLF;
// 	    for ($i = 0; $i <= 12; $i++)
// 	    {
// 	        $html .= '<option value="' . $i . '"';
// 	        if ($this->composite_values['month'] == $i) $html .= ' selected="selected"';
// 	        $html .= '>' . $GLOBALS['mn'][$i] . '</option>' . CRLF;
// 	    }
// 	    $html .= '</select>' . CRLF;

	    if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
	    {
	        $html .= '</div><!-- /.col-xs-6 -->' . CRLF;
	        $html .= '</div><!-- /.row -->' . CRLF;
	    }

	    return $html;
	}

	public function getEmailValue()
	{
	    return sprintf('%02s', $this->composite_values['hour']).':'.sprintf('%02s', $this->composite_values['minute']);
	}

	public function &getObjectValue()
	{
	    $val = $this->getEmailValue();

	    return $val;
	}
}