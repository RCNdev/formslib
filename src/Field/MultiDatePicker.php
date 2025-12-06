<?php
namespace formslib\Field;

use function is_object;

class MultiDatePicker extends GenericMulti
{
    private $preprocessed = false;
    protected $startyear, $endyear;
    protected $startdate, $enddate;
    
    public function __construct($name)
    {
        parent::__construct($name);

        $this->startyear = date('Y');
        $this->endyear = date('Y');

        // $this->addRule('date_format', 'uk', 'Dates must be in the format dd/mm/yyyy');
        // $this->addRule('date_exists', 'uk', 'The date entered was incomplete or does not exist');
	}

    protected function _preProcessValues()
    {
        if (!$this->preprocessed)
        {
            $indices_str = isset($this->multi_values['control']) ? trim($this->multi_values['control']) : '';
            $indices = explode(',', $indices_str);

            foreach ($indices as $i)
            {
                if (preg_match('/^[0-9]+$/', trim($i)))
                {
                    $this->indices[] = trim($i);
                }
            }

            $this->preprocessed = true;
        }
    }

	protected function getSingleInstance($i, $setValue = false)
	{
		$field = new \formslib_datepicker("{$this->name}__{$i}");
		$field->forceOutputStyle($this->outputstyle)
		->addClass('form-control')
		->addAttr('data-index', $i)
        ->set_years($this->startyear, $this->endyear);

		if ($setValue)
		{
			$field->value = $this->multi_values[$i];
		}

        if (isset($this->startdate))
        {
            $field->setStartDate($this->startdate);
        }

        if (isset($this->enddate))
        {
            $field->setEndDate($this->enddate);
        }

		return $field->getHTML() . CRLF;
	}

	public function getJs()
	{
		$js = [];

        $start = '';
        if (isset($this->startdate))
        {
            $start = $this->startdate;
        }
        elseif (isset($this->startyear))
        {
            $start = "01/01/{$this->startyear}";
        }

        $end = '';
        if (isset($this->enddate))
        {
			$end = 'endDate: "'.$this->enddate.'"';
        }
        elseif (isset($this->endyear))
        {
            $end = 'endDate: "31/12/'.$this->endyear.'"';
        }

		$js[] = <<<JS
$(document).on('click', 'a.btn.formslib-multiremove[data-formslib-field="{$this->name}"]', function(e){
	field = $(this).data('formslib-field');
	index = $(this).data('index');

	var indices_str = $('input[name="'+field+'__control"').val();
	indices_str = ','+indices_str+',';
	indices_str = indices_str.replace(','+index+',', ',');
	indices_str = indices_str.substr(1, indices_str.length-2);
	$('input[name="'+field+'__control"').val(indices_str);

	$(this).closest('.formslib-multi-item').remove();
});

$(document).on('click', '.formslib-multiadd a.btn[data-formslib-field="{$this->name}"]', function(e)
{
	field = $(this).data('formslib-field');

	var indices_str = $('input[name="'+field+'__control"').val();

	indices = indices_str.split(',');
	var largest = Math.max.apply(Math, indices);
	var next = largest+1;

	indices.push(next);

	indices_str = indices.join(',');
	$('input[name="'+field+'__control"').val(indices_str);

	var newblock = `
	<div class="col-xs-12 formslib-multi-item">
    	<div class="row"><div class="col-xs-11">
            <div class="input-group date">
                <input type="text" class="form-control" name="{$this->name}__\${next}" id="fld_{$this->name}__\${next}" value="">
                <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            </div>
        </div>
        <div class="col-xs-1">
            <a class="btn btm-sm btn-danger formslib-multiremove" data-formslib-field="{$this->name}" title="Remove" data-index="\${next}"><i class="fa fa-times"></i></a></div>
        </div><!--/.row-->
	</div><!--/.col-xs-12-->
`;

	$(this).parent().before(newblock);

    var start = "{$start}";
    var last = $('input#fld_{$this->name}__'+largest).val();
    start = (last != '') ? last : start;

    $('input#fld_{$this->name}__'+next).parent().datepicker({
        weekStart: 1,
        language: "en-GB",
        format: 'dd/mm/yyyy',
        autoclose: true,
        startDate: start,
        $end
    });

	return false;
});
JS;

		return $js;
	}

    public function &set_years($start, $end)
    {
        $this->startyear = $start;
        $this->endyear = $end;

        // $this->addRule('Dates_UkAfter', '01/01/'.$start, 'Must be after 01/01/'.$start);
        // $this->addRule('Dates_UkBefore', '31/12/'.$end, 'Must be before 31/12/'.$end);

        return $this;
    }

    public function &setStartDate($date)
    {
        $this->startdate = $date;

        // $this->addRule('Dates_UkAfter', $date, 'Must be on or after '.$date);

        return $this;
    }

    public function &setEndDate($date)
    {
        $this->enddate = $date;

        // $this->addRule('Dates_UkBefore', $date, 'Must be on or before '.$date);

        return $this;
    }

    public function getEmailValue()
	{
        throw new \Exception('getEmailValue() not implemented on MultiDatePicker');
	}

	public function &getObjectValue()
	{
		$vals = $this->multi_values;

		unset($vals['control']);

        $dates = [];
        foreach ($vals as $val)
        {
            $date = \DateTime::createFromFormat('!d/m/Y', $val);

            if ($date !== false && is_object($date))
            {
                $dates[] = $date;
            }
        }

		return $dates;
	}
}