<?php
namespace formslib\Field;

class DateSelect extends \formslib_select
{
	protected $dateformat;

	public function &setDateRange($startDate, $endDate)
	{
		$patternDate = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';
		if (!preg_match($patternDate, $startDate) || !preg_match($patternDate, $endDate)) throw new \Exception('Invalid date format');

		if ($startDate > $endDate) throw new \Exception('End date constraint before start date');

		$start = new \DateTime($startDate);
		$end = new \DateTime($endDate);

		$interval = $start->diff($end);
		$daycount = $interval->days;

		if ($daycount < 5) $this->dateformat = 'l';
		elseif ($daycount < 21) $this->dateformat = 'l jS';
		elseif ($daycount >= 21) $this->dateformat = 'l j M';

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

	public function getEmailValue()
	{
		return date($this->dateformat, strtotime($this->value));
	}

	public function &getObjectValue()
	{
	    $date = \DateTime::createFromFormat('!Y-m-d', $this->value);

	    return $date;
	}
}