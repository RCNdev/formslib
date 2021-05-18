<?php
namespace formslib;

use formslib\Utility\Security;

class Fieldset extends \formslib_fieldset
{
    private $name, $legend;
    private $fields = [];
    private $introtext, $footertext;
    private $classes = [];
    private $nohtml;
    private $layout_table = false;
    private $table_columns = [];
    private $htmlbefore, $htmlafter;
    private $legendclass = [];
    private $fieldorder = [];
    private $isRawLegend = false;

    /** @var \formslib\Rule\DisplayCondition */
    private $display_condition;

    public function __construct($name)
    {
        $this->name = $name;
        $this->legend = '';
    }

    public function getName()
    {
    	return $this->name;
    }

    public function &setLegend($legend)
    {
        $this->legend = $legend;

        return $this;
    }

    public function attachField($name)
    {
        $this->fields[] = $name;
        $this->fieldorder[] = $name;
    }

    public function display(Form &$form, $submitbutton = false, $encasing_html = true, $readOnly = false)
    {
        echo $this->htmlbefore;

        echo CRLF;
        if ($this->nohtml == true) $encasing_html = false;

        if ($encasing_html)
        {
            $lc = (count($this->legendclass)) ? ' class="'.implode(' ', $this->legendclass).'"' : '';

            echo '<fieldset name="' . $this->name . '"' . $this->_class_attr() . ' data-formslib-owner="fs_' . $this->name . '">' . CRLF;

            $legend = ($this->isRawLegend) ? $this->legend : Security::escapeHtml($this->legend);

            echo '<legend'.$lc.'>' . $legend . '</legend>' . CRLF . CRLF;
        }

        echo $this->introtext . CRLF . CRLF;

        if (! $this->layout_table)
        {
            foreach ($this->fieldorder as $fieldname)
            {
                if (is_object($form->fields[$fieldname]))
                {
                    if (!$readOnly)
                    {
                        $form->fields[$fieldname]->display($form);
                    }
                    else
                    {
                        $form->fields[$fieldname]->displayReadOnly($form);
                    }
                }
                else
                {
                    throw new \Exception('Field ' . $fieldname . ' is not an object.');
                }
            }
        }
        else
        {
            // TODO: Work out the column spacing first

            echo CRLF;
            echo '<div class="table-responsive">'.CRLF;
            echo '<table class="table table-hover table-bordered table-condensed">'.CRLF;
            echo '<thead>' . CRLF;
            echo '<tr>' . CRLF;
            foreach ($this->table_columns as $column)
            {
                $thclass = (isset($column['class'])) ? ' class="'.$column['class'].'"' : '';
                echo '<th'.$thclass.'>'.CRLF;
                echo Security::escapeHtml($column['label']);
                echo '</th>' . CRLF;
            }
            echo '</tr>' . CRLF;
            echo '</thead>' . CRLF;
            echo '<tbody>' . CRLF;

            $first = true;
            $outputted = false;
            foreach ($this->fieldorder as $fieldname)
            {
                if (is_object($form->fields[$fieldname]))
                {
                    $fld =& $form->fields[$fieldname];

                    if ($fld->isRowStarter())
                    {
                        if (! $first) echo '</tr>' . CRLF . CRLF;

                        echo '<tr>';
                    }

                    $outputted = true;

                    //TODO: Read-only table layout display
                    echo '<td>';
                    echo '<label class="sr-only control-label" for="fld_' . Security::escapeHtml($fieldname) . '">' . Security::escapeHtml($fld->getLabel()) . '</label> ' . CRLF;
                    echo $fld->getHTML($form);
                    // TODO: Mandatory/optional markings?
                    echo '</td>';

                    $first = false;
                }
                else
                {
                    throw new \Exception('Field "' . $fieldname . '" is not an object.');
                }
            }
            if ($outputted) echo '</tr>' . CRLF;
            echo '</tbody>' . CRLF;
            echo '</table>' . CRLF;
            echo '</div><!-- /.table-responsive -->'.CRLF;
            echo CRLF;
        }

        echo $this->footertext . CRLF . CRLF;

        if ($submitbutton === true)
        {
            echo $form->getSubmitHtml();
        }

        if ($encasing_html)
        {
            echo '</fieldset>' . CRLF;
        }

        echo $this->htmlafter;
    }

    public function getDataDump(&$form)
    {
        $data = [];
        foreach ($this->fieldorder as $fieldname)
        {
            $data[$fieldname] = $form->fields[$fieldname]->getDataDump();
        }

        return $data;
    }

    public function &setIntroText($text)
    {
        $this->introtext = $text;

        return $this;
    }

    public function &setFooterText($text)
    {
        $this->footertext = $text;

        return $this;
    }

    public function &appendFooter($html)
    {
        $this->footertext .= $html;

        return $this;
    }

    /**
     * Get the body of an email with the results of the form
     *
     * @param \formslib\Form $form
     * @param integer $style
     * @return string
     */
    public function getEmailBody(&$form, &$style, $includeConditionalDisplay = false)
    {
        $body = '';

        if ($includeConditionalDisplay
            || !is_object($this->display_condition)
            || $this->display_condition->evaluateField($form->getField($this->display_condition->getFieldName())))
        {
            if ($style == FORMSLIB_EMAILSTYLE_HTML || $style == FORMSLIB_EMAILSTYLE_HTML_TH)
            {
                $body .= '<h2>' . Security::escapeHtml($this->legend) . '</h2>' . CRLF;
                $body .= '<table class="table">' . CRLF;
            }
            elseif ($style == FORMSLIB_EMAILSTYLE_HTML_COLSPAN)
            {
                $body .= '<tr><th colspan="2" style="text-align: center;"><h2>' . Security::escapeHtml($this->legend) . '</h2></th></tr>';
            }
            else
            {
                $body .= CRLF . CRLF . CRLF . '***** ' . $this->legend . ' *****' . CRLF . CRLF;
            }

            foreach ($this->fieldorder as $fieldname)
            {
                $field =& $form->fields[$fieldname];

                if (! $field->getDoNotEmail())
                {
                    $cond = $field->getDisplayCondition();

                    if ($includeConditionalDisplay)
                    {
                        $displayed = true;
                    }
                    elseif (is_object($cond))
                    {
                        $displayed = $cond->evaluateField($form->getField($cond->getFieldName()));
                    }
                    else
                    {
                        $displayed = true;
                    }

                    if ($displayed)
                    {
                        switch ($style)
                        {
                            case FORMSLIB_EMAILSTYLE_HTML:
                            case FORMSLIB_EMAILSTYLE_HTML_TH:
                            case FORMSLIB_EMAILSTYLE_HTML_COLSPAN:
                                $cell = ($style == FORMSLIB_EMAILSTYLE_HTML_TH || FORMSLIB_EMAILSTYLE_HTML_COLSPAN) ? 'th' : 'td';
                                $body .= '<tr>' . CRLF;
                                $body .= '<' . $cell . '>' . $field->getLabelInnerHtml() . '</' . $cell . '>' . CRLF;
                                $body .= '<td>' . str_replace("\n", "<br />\n", Security::escapeHtml($field->getEmailValue())) . '</td>' . CRLF;
                                $body .= '</tr>' . CRLF;
                                break;

                            default:
                                $body .= $fieldname . ':' . CRLF;
                                $body .= $field->getEmailValue() . CRLF . CRLF;
                                break;
                        }
                    }
                }
            }

            if ($style == FORMSLIB_EMAILSTYLE_HTML || $style == FORMSLIB_EMAILSTYLE_HTML_TH)
            {
                $body .= '</table>' . CRLF;
            }
        }

        return $body;
    }

    public function unattachField($fieldname)
    {
        if (! is_array($fieldname)) $fieldname = [$fieldname];

        foreach ($fieldname as $fn)
        {
            $index = array_search($fn, $this->fields);
            if ($index === false) return false; // TODO: Throw exception on attempting to remove a non-existent field?

            unset($this->fields[$index]);
            unset($this->fieldorder[$index]);
        }
    }

    public function &addClass($classname)
    {
        $this->classes[] = $classname;

        return $this;
    }

    private function _class_attr()
    {
        $class_str = '';
        if (count($this->classes) > 0)
        {
            $class_str .= ' class="';
            $first = true;
            foreach ($this->classes as $classname)
            {
                if (! $first) $class_str .= ' ';
                $class_str .= $classname;
                $first = false;
            }
            $class_str .= '"';
        }

        return $class_str;
    }

    public function &setNoHTMLWrapper($nowrap = true)
    {
        $this->nohtml = $nowrap;

        return $this;
    }

    public function &setTableLayout($layout_table = true)
    {
        $this->layout_table = $layout_table;

        return $this;
    }

    public function &setTableColumns(array $columns, $merge = false)
    {
        if (!$merge)
            $this->table_columns = [];

            $this->table_columns += $columns;

            return $this;
    }

    public function &setHTMLBefore($html)
    {
        $this->htmlbefore = $html;

        return $this;
    }

    public function &setHTMLAfter($html)
    {
        $this->htmlafter = $html;

        return $this;
    }

    public function &addLegendClass($class)
    {
        $this->legendclass[] = $class;

        return $this;
    }

    public function getJs(Form &$form)
    {
        $js = [];

        foreach ($this->fields as $fieldname)
        {
            if (is_object($form->fields[$fieldname]))
            {
                $js = array_merge($js, $form->fields[$fieldname]->getJs());
            }
        }

        return $js;
    }

    public function getFieldCount()
    {
        return count($this->fields);
    }

    /**
     * Build a result object for the form from local fields
     *
     * @param \formslib\Form $form
     * @param object $result
     */
    public function buildResultObject(&$form, &$result, $includeConditionalDisplay)
    {
        if ($includeConditionalDisplay
            || !is_object($this->display_condition)
            || $this->display_condition->evaluateField($form->getField($this->display_condition->getFieldName())))
        {

            foreach ($this->fields as $fieldname)
            {
                $field =& $form->fields[$fieldname];

                if (! $field->getNoObject())
                {
                    $cond = $field->getDisplayCondition();

                    if ($includeConditionalDisplay)
                    {
                        $displayed = true;
                    }
                    elseif (is_object($cond))
                    {
                        $displayed = $cond->evaluateField($form->getField($cond->getFieldName()));
                    }
                    else
                    {
                        $displayed = true;
                    }

                    if ($displayed) $result->{$fieldname} = $field->getObjectValue();
                }
            }
        }
    }

    public function positionFieldTo($fieldname, $location)
    {
        if ($location < 1 || $location > count($this->fieldorder)) throw new \Exception('Invalid field position');

        $oldpos = (array_search($fieldname, $this->fieldorder));

        if ($oldpos === false) throw new \Exception('Field not found');

        unset($this->fieldorder[$oldpos]);

        array_splice($this->fieldorder, $location-1, 0, [$fieldname]);
    }

    public function positionFieldBelow($fieldname, $below)
    {
        $orig = array_search($fieldname, $this->fieldorder);
        if ($orig === false) throw new \Exception('Field not found');

        $pos = array_search($below, $this->fieldorder);
        if ($pos === false) throw new \Exception('Reference field not found');

        $location = ($pos >= $orig) ? $pos + 1 : $pos + 2;

        $this->positionFieldTo($fieldname, $location);
    }

    public function positionFieldAbove($fieldname, $above)
    {
        $orig = array_search($fieldname, $this->fieldorder);
        if ($orig === false) throw new \Exception('Field not found');

        $pos = array_search($above, $this->fieldorder);
        if ($pos === false) throw new \Exception('Reference field not found');

        $location = ($pos > $orig) ? $pos : $pos + 1;

        $this->positionFieldTo($fieldname, $location);
    }

    public function &setIsRawLegend($raw = true)
    {
        $this->isRawLegend = $raw;

        return $this;
    }

    public function &setDisplayCondition(\formslib\Rule\DisplayCondition $condition)
    {
        $this->display_condition = $condition;

        return $this;
    }

    /**
     * Gets any display condition object associated with this fieldset
     *
     * @return \formslib\Rule\DisplayCondition
     */
    public function getDisplayCondition()
    {
        return $this->display_condition;
    }

    public function hasField($name)
    {
        return (in_array($name, $this->fields));
    }

    public function displayReadOnly(Form &$form)
    {
        $this->display($form, false, false, true);
    }
}