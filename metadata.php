<?php
/**
 * Dummy abstract class to cover all field types for autocomplete purposes
 *
 * @author sheppardp
 */
abstract class formslib_field_paramset extends formslib_field
{
	public function &setLabel($label)
	{
		return $this;
	}

	public function &setValue($val)
	{
		return $this;
	}

	public function &addOption($value, $label)
	{
		return $this;
	}

	public function &setOptions($in_opt)
	{
		return $this;
	}

	public function &setOptionsRange($start, $end)
	{
		return $this;
	}

	public function &addLabelClass($class)
	{
		return $this;
	}

	public function &setTickBefore()
	{
		return $this;
	}

	public function &setTickAfter()
	{
		return $this;
	}

	public function &setTrueText($text)
	{
		return $this;
	}

	public function &setFalseText($text)
	{
		return $this;
	}

	public function &setRawBoxOnly()
	{
		return $this;
	}

	public function &setCheckedValue($checkedvalue)
	{
		return $this;
	}

	public function &set_years($start, $end)
	{
		return $this;
	}

	public function &setTickList($lookup)
	{
		return $this;
	}

	public function &setYear($year)
	{
		return $this;
	}

	public function &setDelimiter($delimiter)
	{
		return $this;
	}

	public function &addButtonClass($class)
	{
		return $this;
	}

	public function &setButtonText($text)
	{
		return $this;
	}

	public function &setStartDate($date)
	{
		return $this;
	}

	public function &setEndDate($date)
	{
		return $this;
	}

	public function &setInputGroupLeft($type, $html)
	{
		return $this;
	}

	public function &setInputGroupRight($type, $html)
	{
		return $this;
	}

	public function &setDateRange($startDate, $endDate)
	{
		return $this;
	}

	public function &setTimeRange($startTime, $endTime)
	{
		return $this;
	}

	public function &setButtons($buttons)
	{
		return $this;
	}

	public function &setMaxLevels($levels)
	{
		return $this;
	}

	public function &setClientSideData($clientside)
	{
		return $this;
	}

	public function &setStartEnd($startMonth, $startYear, $endMonth, $endYear)
	{
		return $this;
	}

	public function &setReturnType($type)
	{
		return $this;
	}
}
