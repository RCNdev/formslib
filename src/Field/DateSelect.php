<?php
namespace formslib\Field;

class DateSelect extends \formslib_select
{
	protected $dateformat = '';

	/**
	 * Set the date range for the dropdown
	 *
	 * @param string $startDate Start date as a string that can be passed to \DateTime
	 * @param string $endDate End date as a string that can be passed to \DateTime
	 * @param string $customFormat
	 *
	 * @return $this
	 */
	public function &setDateRange($startDate, $endDate, $customFormat = null)
	{
		$patternDate = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';

		if (!preg_match($patternDate, $startDate) || !preg_match($patternDate, $endDate))
		{
			throw new \UnexpectedValueException('Invalid date format');
		}

		if ($startDate > $endDate)
		{
			throw new \UnexpectedValueException('End date constraint before start date');
		}

		$start = new \DateTime($startDate);
		$end = new \DateTime($endDate);

		$interval = $start->diff($end);
		$daycount = $interval->days;

		if (!is_null($customFormat))
		{
			$this->dateformat = $customFormat;
		}
		elseif ($daycount < 5)
		{
			$this->dateformat = 'l';
		}
		elseif ($daycount < 21)
		{
			$this->dateformat = 'l jS';
		}
		elseif ($daycount >= 21)
		{
			$this->dateformat = 'l j M';
		}

		$days = [];
		for ($i = 0; $i <= $daycount; $i++)
		{
			$start = new \DateTime($startDate);
			$date = $start->add(new \DateInterval('P'.$i.'D'));
			$key = $date->format('Y-m-d');
			$days[$key] = $date->format($this->dateformat);
		}

		$this->setOptions(['' => '- Select day -'] + $days);

		return $this;
	}

	/**
	 * {@inheritDoc}
	 * @see \formslib_options::getEmailValue()
	 * @return string
	 */
	public function getEmailValue()
	{
		return date($this->dateformat, strtotime($this->value));
	}

	/**
	 * {@inheritDoc}
	 * @see \formslib_options::getObjectValue()
	 * @return \DateTime
	 */
	public function &getObjectValue()
	{
	    $date = \DateTime::createFromFormat('!Y-m-d', $this->value);

	    return $date;
	}
}