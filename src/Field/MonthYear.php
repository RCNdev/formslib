<?php
namespace formslib\Field;

class MonthYear extends Composite
{
	protected $startMonth, $startYear, $endMonth, $endYear;
	protected $returnType;

	/** @var \formslib_select */
	protected $fieldMonth, $fieldYear;

	protected $months = array(
			'01' => 'January',
			'02' => 'February',
			'03' => 'March',
			'04' => 'April',
			'05' => 'May',
			'06' => 'June',
			'07' => 'July',
			'08' => 'August',
			'09' => 'September',
			'10' => 'October',
			'11' => 'November',
			'12' => 'December',
	);

	const RETURN_TYPE_START = 1;
	const RETURN_TYPE_END = 2;
	const RETURN_TYPE_OBJECT = 4;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->_set_composites(array(
			'month',
			'year'
		));

		$this->fieldMonth = new \formslib_select($name.'__month');
		$this->fieldMonth->setOptions(array('' => '- Month -') + $this->months);

		$this->fieldYear = new \formslib_select($name.'__year');
	}

	protected function _prepareOutput()
	{
		$this->fieldMonth->value = $this->composite_values['month'];
		$this->fieldYear->value = $this->composite_values['year'];

		$this->fieldMonth->addClasses($this->getClasses());
		$this->fieldYear->addClasses($this->getClasses());

		$this->fieldMonth->setAttributes($this->attrib);
		$this->fieldYear->setAttributes($this->attrib);
	}

	public function getHTML()
	{
		$this->_prepareOutput();

		$html = '';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '<div class="row">' . CRLF;
			$html .= '<div class="col-xs-6">' . CRLF;
		}
		else
		{
			$this->fieldYear->addClass('input-mini');
		}

		// Month
		$html .= $this->fieldMonth->getHTML();

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-xs-6">' . CRLF;
		}
		else
		{
			$this->fieldYear->addClass('input-small');
			$this->fieldYear->addClass('col-sm-6');
		}

		// Year
		$html .= $this->fieldYear->getHTML();

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div><!-- /.col-xs-6 -->' . CRLF;
			$html .= '</div><!-- /.row -->' . CRLF;
		}

		return $html;
	}

	public function &setStartEnd($startMonth, $startYear, $endMonth, $endYear)
	{
		$this->startMonth = $startMonth;
		$this->startYear = $startYear;
		$this->endMonth = $endMonth;
		$this->endYear = $endYear;

		$this->fieldYear->setOptionsRange($startYear, $endYear, '- Year -');

		$this->addRule('Composite_MonthYearMin', array('month' => $startMonth, 'year' => $startYear), 'Cannot be earlier than '.$this->months[$startMonth].' '.$startYear);
		$this->addRule('Composite_MonthYearMax', array('month' => $endMonth, 'year' => $endYear), 'Cannot be later than '.$this->months[$endMonth].' '.$endYear);

		return $this;
	}

	public function &setReturnType($type)
	{
		$this->returnType = $type;

		return $this;
	}

	public function getEmailValue()
	{
		if ($this->composite_values['year'] == '' || $this->composite_values['month'] == '') return '';

		return $this->months[$this->composite_values['month']] . ' ' . $this->composite_values['year'];
	}

	public function &getObjectValue()
	{
		if ($this->composite_values['year'] == '' || $this->composite_values['month'] == '') return null;

		if ($this->returnType & self::RETURN_TYPE_OBJECT)
		{
			if ($this->returnType & self::RETURN_TYPE_START)
			{
				$date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->composite_values['year'].'-'.$this->composite_values['month'].'-01 00:00:00');
			}
			elseif ($this->returnType & self::RETURN_TYPE_END)
			{
				$date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->composite_values['year'].'-'.$this->composite_values['month'].'-01 00:00:00');
				$date->modify('last day of this month');
			}
			else
			{
				throw new \Exception('Object return requested, but start or end day not specified');
			}
		}
		else
		{
			$date = $this->getEmailValue();
		}

		return $date;
	}
}