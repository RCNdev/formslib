<?php
namespace formslib\Field;

use formslib\Utility\Security;

class TickList extends Composite
{
    private $ticklist = [];
    private $checkedvalue = 'checked';
    private $delimiter = "\n";
    private $enableSelectAll = false;
    private $selectAllText = null;

    public function __construct($name)
    {
        parent::__construct($name);
    }

    public function &setTickList($lookup)
    {
        $this->ticklist = $lookup;
        $this->_set_composites(array_keys($lookup));

        return $this;
    }

    public function getHTML()
    {
        if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3_VERTICAL)
        {
            $html = '<div class="formslib_ticklist_container">';
        }
        else
        {
            // TODO: Inline CSS, get this into a proper style sheet!
            $html = '<span class="formslib_ticklist_container" style="display: block; float: left;">';
        }

        if ($this->enableSelectAll && count($this->ticklist) > 1)
        {
            $html .= '<span class="formslib_ticklist_select_all"><a href="#">'.Security::escapeHtml($this->selectAllText).'</a></span>';
        }

        foreach ($this->ticklist as $index => $label)
        {
            $checked = ($this->composite_values[$index] == $this->checkedvalue) ? ' checked="checked"' : '';

            $text = Security::escapeHtml($label) . CRLF;

            $input = '';

            if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3_VERTICAL)
            {
                $html .= '<div>';
            }

            $input .= '<input type="checkbox" value="' . $this->checkedvalue . '"' . $checked . ' ' . $this->_custom_attr() . $this->_class_attr('formslib_ticklist') . ' name="' . Security::escapeHtml($this->name . '__' . $index) . '" id="fld_' . Security::escapeHtml($this->name . '__' . $index) . '" title="' . Security::escapeHtml($label) . '" />' . CRLF;

            // TODO: More inline CSS
            $html .= '<label for="fld_'.$this->name.'__'.$index.'" class="formslib_label_checkbox" style="display: inline; font-weight: normal;">';
            $html .= $input . $text;
            $html .= '</label>';

            if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3_VERTICAL)
            {
                $html .= '</div><!--/div-->'.CRLF;
            }
            else
            {
                $html .= '<br />'.CRLF;
            }
        }

        if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3_VERTICAL)
        {
            $html .= '</div><!--/.formslib_ticklist_container-->';
        }
        else
        {
            $html .= '</span><!--/.formslib_ticklist_container-->';
            $html .= '<span style="display: block; clear: both;"></span>'.CRLF;
        }

        return $html;
    }

    public function getHTMLReadOnly()
    {
        $html = '<span class="formslib_ticklist_container" style="display: block; float: left;">';

        foreach ($this->ticklist as $index => $label)
        {
            $ids = 'name="' . $this->name . '" id="fld_' . Security::escapeHtml($this->name) . '"';
            $checked = ($this->composite_values[$index] == $this->checkedvalue) ? '<span ' . $ids . ' class="colour-positive">&#10004;</span>' : '<span ' . $ids . ' class="colour-negative">&#10008;</span>';

            $text = Security::escapeHtml($label) . CRLF;

            $html .= $checked . $text . '<br />' . CRLF;
        }

        $html .= '</span>';

        $html .= '<span style="display: block; clear: both;"></span>';

        return $html;
    }

    public function getEmailValue()
    {
        $checked_vals = [];
        foreach ($this->composites as $value)
        {
            if (isset($this->composite_values[$value]) && $this->composite_values[$value] == $this->checkedvalue) $checked_vals[] = $this->ticklist[$value];
        }

        if (! count($checked_vals))
        {
            return 'No options selected';
        }
        else
        {
            return implode($this->delimiter, $checked_vals);
        }
    }

    public function &setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;

        return $this;
    }

    public function &getObjectValue()
    {
        $checked = [];

        foreach ($this->composites as $value)
        {
            if (isset($this->composite_values[$value]) && $this->composite_values[$value] == $this->checkedvalue) $checked[$value] = $this->ticklist[$value];
        }

        return $checked;
    }

    public function &setSelectAll($text = 'Select all')
    {
        if ($text === false)
        {
            $this->enableSelectAll = false;
        }
        else
        {
            $this->enableSelectAll = true;
            $this->selectAllText = $text;
        }

        return $this;
    }

    public function getJs()
    {
        $js = parent::getJs();

        if ($this->enableSelectAll)
        {
            $js[] = <<<JS
$(document).ready(function(){
	$('.formslib_ticklist_select_all a').click(function(){
        $(this).parents('.formslib_ticklist_container').find('input.formslib_ticklist').prop('checked', true);
        $(this).parents('.formslib_ticklist_select_all').hide();
		return false;
	});
});
JS;
        }

        return $js;
    }

    public function checkMandatoryVars(array &$vars)
    {
        $something_ticked = false;

        foreach ($this->composites as $key)
        {
            if (isset($vars[$this->name . '__' . $key]) && $vars[$this->name . '__' . $key] === $this->checkedvalue) $something_ticked = true;
        }

        return $something_ticked;
    }
}