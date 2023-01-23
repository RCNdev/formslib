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

        $this->setTimeRange('00:00', '23:59');
    }

    /**
     *
     * @param string $startTime
     * @param string $endTime
     * @throws \Exception
     * @return \formslib\Field\TimeSelect
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
	    }

	    $this->field_hour->addClasses($this->getClasses());
	    $this->field_hour->value = $this->composite_values['hour'];
	    $html .= $this->field_hour->getHTML();

	    if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
	    {
	        $html .= '</div>' . CRLF;
	        $html .= '<div class="col-xs-4">' . CRLF;
	    }

	    $this->field_minute->addClasses($this->getClasses());
	    $this->field_minute->value = $this->composite_values['minute'];
	    $html .= $this->field_minute->getHTML();

	    if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
	    {
	        $html .= '</div><!-- /.col-xs-6 -->' . CRLF;
	        $html .= '</div><!-- /.row -->' . CRLF;
	    }

	    return $html;
	}

	public function getEmailValue()
	{
	    if ($this->composite_values['hour'] === '' && $this->composite_values['minute'] === '') return null;

	    return sprintf('%02s', $this->composite_values['hour']).':'.sprintf('%02s', $this->composite_values['minute']);
	}

	public function &getObjectValue()
	{
	    $val = $this->getEmailValue();

	    return $val;
	}
}