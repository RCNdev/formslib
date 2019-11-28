<?php
namespace formslib\Field;

use formslib\Form;
use formslib\Utility\Security;
use formslib\Fieldset;

/**
 * Abstract field class for formslib
 * @author sheppardp
 */
abstract class Field
{
    protected $name, $defaultval;
    public $label, $value;
    public $mandatory = false, $semimandatory = false;
    protected $rules = [];
    protected $classes = [];
    protected $attrib = [];
    protected $labelclass = [];
    public $valid;
    protected $errorlist = [];
    protected $rawoutput = false;
    protected $htmlbefore, $htmlafter, $innerhtmlbefore, $innerhtmlafter, $helpinline, $helpblock, $helpbefore = false;
    protected $donotemail = false, $noObject = false;
    protected $group_classes = [];
    protected $gridRatio = 3;
    protected $starts_new_row = false;
    protected $outputstyle = null;
    protected $ajaxFormIdentifier;
    protected $disabled = false;

    /** @var \formslib\Rule\DisplayCondition */
    protected $display_condition;

    public function __construct($name)
    {
        $this->name = $name;
        $this->addClass('formslib_'.$this->getType());
    }

    public static function &create($name, Form &$f, Fieldset &$fs = null)
    {
        $field = new static($name);
        $f->attachField($field);
        if (is_object($fs)) $fs->attachField($name);

        return $field;
    }

    /**
     * Get the name of the field
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the type name the form class recognises to add this field
     *
     * @return string
     */
    public function getType()
    {
        $type = get_class($this);

        if (substr($type, 0, 9) == 'formslib_') $type = substr($type, 9);
        elseif (substr($type, 0, 15) == 'formslib\Field\\') $type = substr($type, 15);

        return $type;
    }

    public function &setDefault($default)
    {
        $this->defaultval = $default;

        return $this;
    }

    public function &setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    public function &setMandatory($mandatory = true)
    {
        $this->mandatory = $mandatory;

        if ($mandatory)
        {
            $this->classes[] = 'formslib_jq_mand';
        }
        else
        {
            if (isset($this->classes['formslib_jq_mand']))
            {
                unset($this->classes['formslib_jq_mand']);
            }
        }

        return $this;
    }

    public function &setSemiMandatory($mandatory = true)
    {
        $this->semimandatory = $mandatory;

        return $this;
    }

    public function &setRawOutput($raw = true)
    {
        $this->rawoutput = $raw;

        return $this;
    }

    public function &addRule($ruletype, $ruledfn, $errormessage, $return_rule_oject = false)
    {
        $ruleclassnamespace = 'formslib\Rule\\'.str_replace('_', '\\', $ruletype);
        $ruleclass = 'formslib_rule_' . $ruletype;

        if (class_exists($ruleclassnamespace))
        {
            $therule = new $ruleclassnamespace($ruledfn, $errormessage, $this);
            if ($therule !== false)
            {
                $this->rules[] = &$therule;
            }

            if ($return_rule_oject) return $therule;
        }
        elseif (class_exists($ruleclass))
        {
            $therule = new $ruleclass($ruledfn, $errormessage, $this);
            if ($therule !== false)
            {
                $this->rules[] = &$therule;
            }

            if ($return_rule_oject) return $therule;
        }
        else
        {
            throw new \Exception('Failed to add rule');
        }

        return $this;
    }

    public function &addLabelClass($class)
    {
        $this->labelclass[] = $class;

        return $this;
    }

    public function &addClass($classname)
    {
        $this->classes[] = $classname;

        return $this;
    }

    public function &addAttr($attr, $value)
    {
        $this->attrib[$attr] = $value;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attrib;
    }


    public function &setAttributes($attrib)
    {
        $this->attrib = $attrib;

        return $this;
    }

    public function display(Form &$form)
    {
        $this->outputstyle = $outputstyle = $form->outputstyle;

        $mand = $optionalLabel = null;
        if ($form->getOptionalLabels())
        {
            if (!$this->mandatory) $optionalLabel = $form->optionalHTML;
        }
        else
        {
            if ($this->mandatory)
            {
                $mand = $form->mandatoryHTML;
            }
            elseif ($this->semimandatory)
            {
                $mand = $form->semimandatoryHTML;
            }
        }

        if (! $this->rawoutput)
        {
            switch ($outputstyle)
            {
                case FORMSLIB_STYLE_P:
                    echo $this->htmlbefore;
                    echo '<p>' . CRLF;
                    echo $this->innerhtmlbefore;
                    echo '<label for="fld_' . Security::escapeHtml($this->name) . '">' . Security::escapeHtml($this->label) . $optionalLabel . '</label> ' . CRLF;
                    echo $this->getHTML() . CRLF;
                    echo $mand;
                    echo $this->innerhtmlafter;
                    echo '</p>' . CRLF . CRLF;
                    echo $this->htmlafter;
                    break;

                case FORMSLIB_STYLE_DL:
                default:
                    echo $this->htmlbefore;
                    echo '<dl>' . CRLF;
                    echo '<dt><label for="fld_' . Security::escapeHtml($this->name) . '">' . Security::escapeHtml($this->label) . $optionalLabel . '</label></dt>' . CRLF;
                    echo '<dd>' . $this->getHTML() . $mand . '</dd>' . CRLF;
                    echo '</dl>' . CRLF . CRLF;
                    echo $this->htmlafter;
                    break;

                case FORMSLIB_STYLE_BOOTSTRAP:
                    $group_class_str = implode(' ', $this->group_classes);
                    if ($group_class_str != '') $group_class_str = ' ' . $group_class_str; // Prepend a space

                    echo $this->htmlbefore . CRLF;
                    echo '<div class="control-group' . $group_class_str . '">' . CRLF;
                    echo $this->innerhtmlbefore . CRLF;
                    echo '	<label class="control-label" for="fld_' . Security::escapeHtml($this->name) . '">' . Security::escapeHtml($this->label) . $optionalLabel . '</label> ' . CRLF;
                    echo '	<div class="controls">' . CRLF;
                    echo '		' . $this->getHTML() . CRLF;
                    echo '		' . $mand . CRLF;
                    if ($this->helpinline) echo '		<span class="help-inline">' . $this->helpinline . '</span>' . CRLF;
                    if ($this->helpblock) echo '		<span class="help-block">' . $this->helpblock . '</span>' . CRLF;
                    echo $this->innerhtmlafter . CRLF;
                    echo '	</div><!--/.controls-->' . CRLF;
                    echo '</div><!--/.control-group-->' . CRLF;
                    echo $this->htmlafter . CRLF;
                    break;

                case FORMSLIB_STYLE_BOOTSTRAP3:
                    $col_label = ($this->gridRatio > 0) ? $this->gridRatio : 12;
                    $col_field = 12 - $this->gridRatio;

                    $group_class_str = implode(' ', $this->group_classes);
                    if ($group_class_str != '') $group_class_str = ' ' . $group_class_str; // Prepend a space

                    if (! isset($this->classes['form-control']) && get_class($this) != 'formslib_ticklist') $this->addClass('form-control');

                    echo $this->htmlbefore . CRLF;
                    echo '<div class="form-group' . $group_class_str . '" data-formslib-owner="fld_' . Security::escapeHtml($this->name) . '">' . CRLF;
                    echo $this->innerhtmlbefore . CRLF;
                    echo '	<label class="control-label col-sm-' . $col_label . '" for="fld_' . Security::escapeHtml($this->name) . '">' . Security::escapeHtml($this->label) . $mand. $optionalLabel . '</label> ' . CRLF;
                    echo '	<div class="col-sm-' . $col_field . '">' . CRLF;
                    echo '		' . $this->getHTML() . CRLF;
                    if ($this->helpinline) echo '		<span class="help-block">' . $this->helpinline . '</span>' . CRLF; // TODO: Something better with this
                    if ($this->helpblock) echo '		<span class="help-block">' . $this->helpblock . '</span>' . CRLF;
                    echo $this->innerhtmlafter . CRLF;
                    echo '	</div><!--/.col-sm-' . $col_field . '-->' . CRLF;
                    echo '</div><!--/.form-group-->' . CRLF;
                    echo $this->htmlafter . CRLF;
                    break;

                case FORMSLIB_STYLE_BOOTSTRAP3_INLINE:
                    $group_class_str = implode(' ', $this->group_classes);
                    if ($group_class_str != '') $group_class_str = ' ' . $group_class_str; // Prepend a space

                    if (! isset($this->classes['form-control']) && get_class($this) != 'formslib_ticklist') $this->addClass('form-control');

                    echo $this->htmlbefore . CRLF;
                    echo '<div class="form-group' . $group_class_str . '" data-formslib-owner="fld_' . Security::escapeHtml($this->name) . '">' . CRLF;
                    echo $this->innerhtmlbefore . CRLF;
                    echo '	<label class="control-label" for="fld_' . Security::escapeHtml($this->name) . '">' . Security::escapeHtml($this->label) . $mand . $optionalLabel . '</label> ' . CRLF;
                    // 					echo '	<div class="col-sm-' . $col_field . '">' . CRLF;
                    echo '		' . $this->getHTML() . CRLF;
                    // 					if ($this->helpinline) echo '		<span class="help-block">' . $this->helpinline . '</span>' . CRLF; // TODO: Something better with this
                    // 					if ($this->helpblock) echo '		<span class="help-block">' . $this->helpblock . '</span>' . CRLF;
                    echo $this->innerhtmlafter . CRLF;
                    // 					echo '	</div><!--/.col-sm-' . $col_field . '-->' . CRLF;
                    echo '</div><!--/.form-group-->' . CRLF;
                    echo $this->htmlafter . CRLF;
                    break;

                case FORMSLIB_STYLE_BOOTSTRAP3_VERTICAL:
                    $group_class_str = implode(' ', $this->group_classes);
                    if ($group_class_str != '') $group_class_str = ' ' . $group_class_str; // Prepend a space

                    if (! isset($this->classes['form-control']) && get_class($this) != 'formslib_ticklist') $this->addClass('form-control');

                    echo $this->htmlbefore . CRLF;
                    echo '<div class="form-group' . $group_class_str . '" data-formslib-owner="fld_' . Security::escapeHtml($this->name) . '">' . CRLF;
                    echo $this->innerhtmlbefore . CRLF;

                    echo '	<label class="control-label" for="fld_' . Security::escapeHtml($this->name) . '">' . Security::escapeHtml($this->label) . $mand . $optionalLabel . '</label> ' . CRLF;

                    if ($this->helpblock && $this->helpbefore) echo '		<p class="help-block">' . $this->helpblock . '</p>' . CRLF;

                    echo '		' . $this->getHTML() . CRLF;

                    if ($this->helpinline) echo '		<span class="help-block">' . $this->helpinline . '</span>' . CRLF; // TODO: Something better with this
                    if ($this->helpblock && !$this->helpbefore) echo '		<p class="help-block">' . $this->helpblock . '</p>' . CRLF;

                    echo $this->innerhtmlafter . CRLF;

                    echo '</div><!--/.form-group-->' . CRLF;
                    echo $this->htmlafter . CRLF;
                    break;
            }
        }
        else
        {
            echo $this->htmlbefore;
            echo $this->getHTML() . $mand . CRLF;
            echo $this->htmlafter;
        }
    }

    protected function _class_attr($extraclass = '')
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

    protected function _custom_attr()
    {
        $attr_str = '';
        foreach ($this->attrib as $attrib => $value)
        {
            $attr_str .= ' ' . $attrib . '="' . $value . '"';
        }

        return $attr_str;
    }

    public function getHTML()
    {
        // Skeleton function
        return '';
    }

    public function getHTMLReadOnly()
    {
        return $this->getHTML();
    }

    public function validate($value)
    {
        $valid = true;
        $rules = array_keys($this->rules);

        foreach ($rules as $rule)
        {
            $rulevalid = $this->rules[$rule]->evaluate($value);

            if (! $rulevalid)
            {
                $valid = false;
                $this->errorlist[] = [
                    'name' => $this->name,
                    'label' => $this->label,
                    'message' => $this->label . ' invalid: ' . $this->rules[$rule]->getError()
                ];
            }
        }
        return $valid;
    }

    /**
     * Return validation errors
     *
     * @return array
     */
    public function getErrors()
    {
        return $this->errorlist;
    }

    public function &addBefore($html)
    {
        $this->htmlbefore = $html;

        return $this;
    }

    public function &addAfter($html)
    {
        $this->htmlafter = $html;

        return $this;
    }

    public function getEmailValue()
    {
        return $this->value;
    }

    public function &setDoNotEmail()
    {
        $this->donotemail = true;

        return $this;
    }

    public function getDataDump()
    {
        $data = [];

        $data['type'] = str_replace('formslib_', '', get_class($this));
        $data['name'] = $this->name;
        $data['label'] = $this->label;
        $data['value'] = $this->value;

        return $data;
    }

    public function getDoNotEmail()
    {
        return $this->donotemail;
    }

    public function getLabel()
    {
        return $this->label;
    }

    public function get_jquery_conditions()
    {
        $rules = array_keys($this->rules);
        $rulejs = [];

        foreach ($rules as $rule)
        {
            $rulejs[] = $this->rules[$rule]->get_jquery_condition();
        }
        return $rulejs;
    }

    public function &setInnerHTMLBefore($html)
    {
        $this->innerhtmlbefore = $html;

        return $this;
    }

    public function &setInnerHTMLAfter($html)
    {
        $this->innerhtmlafter = $html;

        return $this;
    }

    public function &addGroupClass($classname)
    {
        $this->group_classes[] = $classname;

        return $this;
    }

    public function &setHelpInline($help_html)
    {
        $this->helpinline = $help_html;

        return $this;
    }

    /**
     *
     * @param string $help_html
     */
    public function &setHelpBlock($help_html)
    {
        $this->helpblock = $help_html;

        return $this;
    }

    public static function getHeader(&$class)
    {
        $class = __CLASS__;

        return '';
    }

    public function displayReadOnly(Form &$form)
    {
        if ($this->mandatory)
            $mand = $form->mandatoryHTML;
            elseif ($this->semimandatory)
            $mand = $form->semimandatoryHTML;
            else
                $mand = '';

                if (! $this->rawoutput)
                {
                    switch ($form->outputstyle)
                    {
                        case FORMSLIB_STYLE_P:
                            echo $this->htmlbefore;
                            echo '<p data-formslib-owner="fld_' . Security::escapeHtml($this->name) . '">' . CRLF;
                            echo $this->innerhtmlbefore;
                            echo '<label for="fld_' . Security::escapeHtml($this->name) . '">' . Security::escapeHtml($this->label) . '</label> ' . CRLF;
                            echo $this->getHTMLReadOnly() . CRLF;
                            echo $mand;
                            echo $this->innerhtmlafter;
                            echo '</p>' . CRLF . CRLF;
                            echo $this->htmlafter;
                            break;

                        case FORMSLIB_STYLE_DL:
                        default:
                            echo $this->htmlbefore;
                            echo '<dl data-formslib-owner="fld_' . Security::escapeHtml($this->name) . '">' . CRLF;
                            echo '<dt><label for="fld_' . Security::escapeHtml($this->name) . '">' . Security::escapeHtml($this->label) . '</label></dt>' . CRLF;
                            echo '<dd>' . $this->getHTMLReadOnly() . $mand . '</dd>' . CRLF;
                            echo '</dl>' . CRLF . CRLF;
                            echo $this->htmlafter;
                            break;

                        case FORMSLIB_STYLE_BOOTSTRAP:
                            $group_class_str = implode(' ', $this->group_classes);
                            if ($group_class_str != '') $group_class_str = ' ' . $group_class_str; // Prepend a space

                            echo $this->htmlbefore . CRLF;
                            echo '<div class="control-group' . $group_class_str . '" data-formslib-owner="fld_' . Security::escapeHtml($this->name) . '">' . CRLF;
                            echo $this->innerhtmlbefore . CRLF;
                            echo '	<label class="control-label" for="fld_' . Security::escapeHtml($this->name) . '">' . Security::escapeHtml($this->label) . '</label> ' . CRLF;
                            echo '	<div class="controls">' . CRLF;
                            echo '		' . $this->getHTMLReadOnly() . CRLF;
                            echo '		' . $mand . CRLF;
                            if ($this->helpinline) echo '		<span class="help-inline">' . $this->helpinline . '</span>' . CRLF;
                            if ($this->helpblock) echo '		<span class="help-block">' . $this->helpblock . '</span>' . CRLF;
                            echo $this->innerhtmlafter . CRLF;
                            echo '	</div><!--/.controls-->' . CRLF;
                            echo '</div><!--/.control-group-->' . CRLF;
                            echo $this->htmlafter . CRLF;
                            break;

                        case FORMSLIB_STYLE_BOOTSTRAP3:
                            // TODO: @see p.form-control-static

                            $col_label = ($this->gridRatio > 0) ? $this->gridRatio : 12;
                            $col_field = 12 - $this->gridRatio;

                            $group_class_str = implode(' ', $this->group_classes);
                            if ($group_class_str != '') $group_class_str = ' ' . $group_class_str; // Prepend a space

                            if (! isset($this->classes['form-control']) && get_class($this) != 'formslib_ticklist') $this->addClass('form-control');

                            echo $this->htmlbefore . CRLF;
                            echo '<div class="form-group' . $group_class_str . '" data-formslib-owner="fld_' . Security::escapeHtml($this->name) . '">' . CRLF;
                            echo $this->innerhtmlbefore . CRLF;
                            echo '	<label class="control-label col-sm-' . $col_label . '" for="fld_' . Security::escapeHtml($this->name) . '">' . Security::escapeHtml($this->label) . $mand . '</label> ' . CRLF;
                            echo '	<div class="col-sm-' . $col_field . '">' . CRLF;
                            echo '		' . $this->getHTMLReadOnly() . CRLF;
                            if ($this->helpinline) echo '		<span class="help-block">' . $this->helpinline . '</span>' . CRLF; // TODO: Something better with this
                            if ($this->helpblock) echo '		<span class="help-block">' . $this->helpblock . '</span>' . CRLF;
                            echo $this->innerhtmlafter . CRLF;
                            echo '	</div><!--/.col-sm-' . $col_field . '-->' . CRLF;
                            echo '</div><!--/.form-group-->' . CRLF;
                            echo $this->htmlafter . CRLF;
                            break;
                    }
                }
                else
                {
                    echo $this->htmlbefore;
                    echo $this->getHTMLReadOnly() . $mand . CRLF;
                    echo $this->htmlafter;
                }
    }

    public function &attachToFieldset(Fieldset &$fs)
    {
        $fs->attachField($this->name);

        return $this;
    }

    public function &attachToForm(Form &$f)
    {
        $f->attachField($this);

        return $this;
    }

    public function &setGridRatio($label_cols)
    {
        $this->gridRatio = $label_cols;

        return $this;
    }

    public function &setStartsNewRow($new_row = true)
    {
        $this->starts_new_row = $new_row;

        return $this;
    }

    public function isRowStarter()
    {
        return $this->starts_new_row;
    }

    public function &setClasses(array $classes)
    {
        $this->classes = $classes;

        return $this;
    }

    public function &addClasses(array $classes)
    {
        foreach ($classes as $class)
        {
            if (!in_array($class, $this->classes)) $this->classes[] = $class;
        }

        return $this;
    }

    public function getClasses()
    {
        return $this->classes;
    }

    public function getJs()
    {
        return [];
    }

    public function &setAjaxFormIdentifier($ident)
    {
        $this->ajaxFormIdentifier = $ident;

        return $this;
    }

    public function &getObjectValue()
    {
        $value = trim($this->getEmailValue());

        return $value;
    }

    /**
     * Set whether a field should be included in a result object
     *
     * @param string $noObject
     */
    public function &setNoObject($noObject = true)
    {
        $this->noObject = $noObject;

        return $this;
    }

    public function getNoObject()
    {
        return $this->noObject;
    }

    public function &forceOutputStyle($style)
    {
        $this->outputstyle = $style;

        return $this;
    }

    public function &setDisabled($disabled = true)
    {
        $this->disabled = $disabled;

        if ($disabled)
        {
            $this->addAttr('disabled', 'disabled');
        }

        return $this;
    }

    public function &setHelpBefore($before = true)
    {
        $this->helpbefore = $before;

        return $this;
    }

    public static function getJsCommon()
    {

    }

    public function &setDisplayCondition(\formslib\Rule\DisplayCondition $condition)
    {
        $this->display_condition = $condition;

        return $this;
    }

    public function getDisplayCondition()
    {
        return $this->display_condition;
    }

    /**
     * Get a jQuery selector that can be used to target this field
     */
    public function getJquerySelector()
    {
    	return 'input[name='.$this->name.']';
    }

    /**
     * Get a jQuery selector that can be used to target this field when the document is ready
     */
    public function getJquerySelectorOnLoad()
    {
    	return $this->getJquerySelector();
    }
}