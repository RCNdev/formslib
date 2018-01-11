<?php

/**
 * Abstract field class for formslib
 * @author sheppardp
 */
abstract class formslib_field
{
	protected $name, $defaultval;
	public $label, $value;
	public $mandatory = false, $semimandatory = false;
	protected $rules = array();
	protected $classes = array();
	protected $attrib = array();
	public $valid;
	protected $errorlist = array();
	protected $rawoutput = false;
	protected $htmlbefore, $htmlafter, $innerhtmlbefore, $innerhtmlafter, $helpinline, $helpblock;
	protected $donotemail = false, $noObject = false;
	protected $group_classes = array();
	protected $gridRatio = 3;
	protected $starts_new_row = false;
	protected $outputstyle = null;
	protected $ajaxFormIdentifier;
	protected $disabled = false;

	public function __construct($name)
	{
		$this->name = $name;
		$this->addClass(get_class($this));
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
			throw new Exception('Failed to add rule');
		}

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

	public function display(formslib_form &$form)
	{
		$this->outputstyle = $outputstyle = $form->outputstyle;

		if ($this->mandatory)
			$mand = $form->mandatoryHTML;
		elseif ($this->semimandatory)
			$mand = $form->semimandatoryHTML;
		else
			$mand = '';

		if (! $this->rawoutput)
		{
			switch ($outputstyle)
			{
				case FORMSLIB_STYLE_P:
					echo $this->htmlbefore;
					echo '<p>' . CRLF;
					echo $this->innerhtmlbefore;
					echo '<label for="fld_' . htmlspecialchars($this->name) . '">' . htmlspecialchars($this->label) . '</label> ' . CRLF;
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
					echo '<dt><label for="fld_' . htmlspecialchars($this->name) . '">' . htmlspecialchars($this->label) . '</label></dt>' . CRLF;
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
					echo '	<label class="control-label" for="fld_' . htmlspecialchars($this->name) . '">' . htmlspecialchars($this->label) . '</label> ' . CRLF;
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
					echo '<div class="form-group' . $group_class_str . '">' . CRLF;
					echo $this->innerhtmlbefore . CRLF;
					echo '	<label class="control-label col-sm-' . $col_label . '" for="fld_' . htmlspecialchars($this->name) . '">' . htmlspecialchars($this->label) . $mand . '</label> ' . CRLF;
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
					echo '<div class="form-group' . $group_class_str . '">' . CRLF;
					echo $this->innerhtmlbefore . CRLF;
					echo '	<label class="control-label" for="fld_' . htmlspecialchars($this->name) . '">' . htmlspecialchars($this->label) . $mand . '</label> ' . CRLF;
// 					echo '	<div class="col-sm-' . $col_field . '">' . CRLF;
					echo '		' . $this->getHTML() . CRLF;
// 					if ($this->helpinline) echo '		<span class="help-block">' . $this->helpinline . '</span>' . CRLF; // TODO: Something better with this
// 					if ($this->helpblock) echo '		<span class="help-block">' . $this->helpblock . '</span>' . CRLF;
					echo $this->innerhtmlafter . CRLF;
// 					echo '	</div><!--/.col-sm-' . $col_field . '-->' . CRLF;
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
				$this->errorlist[] = array(
					'name' => $this->name,
					'label' => $this->label,
					'message' => $this->label . ' invalid: ' . $this->rules[$rule]->getError()
				);
			}
		}
		return $valid;
	}

	public function getErrors()
	{
		// Return validation errors
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
		$data = array();

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
		$rulejs = array();

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
	 * @return formslib_field
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

	public function displayReadOnly(formslib_form &$form)
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
					echo '<p>' . CRLF;
					echo $this->innerhtmlbefore;
					echo '<label for="fld_' . htmlspecialchars($this->name) . '">' . htmlspecialchars($this->label) . '</label> ' . CRLF;
					echo $this->getHTMLReadOnly() . CRLF;
					echo $mand;
					echo $this->innerhtmlafter;
					echo '</p>' . CRLF . CRLF;
					echo $this->htmlafter;
					break;

				case FORMSLIB_STYLE_DL:
				default:
					echo $this->htmlbefore;
					echo '<dl>' . CRLF;
					echo '<dt><label for="fld_' . htmlspecialchars($this->name) . '">' . htmlspecialchars($this->label) . '</label></dt>' . CRLF;
					echo '<dd>' . $this->getHTMLReadOnly() . $mand . '</dd>' . CRLF;
					echo '</dl>' . CRLF . CRLF;
					echo $this->htmlafter;
					break;

				case FORMSLIB_STYLE_BOOTSTRAP:
					$group_class_str = implode(' ', $this->group_classes);
					if ($group_class_str != '') $group_class_str = ' ' . $group_class_str; // Prepend a space

					echo $this->htmlbefore . CRLF;
					echo '<div class="control-group' . $group_class_str . '">' . CRLF;
					echo $this->innerhtmlbefore . CRLF;
					echo '	<label class="control-label" for="fld_' . htmlspecialchars($this->name) . '">' . htmlspecialchars($this->label) . '</label> ' . CRLF;
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
					echo '<div class="form-group' . $group_class_str . '">' . CRLF;
					echo $this->innerhtmlbefore . CRLF;
					echo '	<label class="control-label col-sm-' . $col_label . '" for="fld_' . htmlspecialchars($this->name) . '">' . htmlspecialchars($this->label) . $mand . '</label> ' . CRLF;
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

	public function &attachToFieldset(formslib_fieldset &$fs)
	{
		$fs->attachField($this->name);

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

	public function setClasses(array $classes)
	{
		$this->classes = $classes;
	}

	public function addClasses(array $classes)
	{
		foreach ($classes as $class)
		{
			if (!in_array($class, $this->classes)) $this->classes[] = $class;
		}
	}

	public function getClasses()
	{
		return $this->classes;
	}

	public function getJs()
	{
		return array();
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
	 * @return formslib_field
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
}


class formslib_hidden extends formslib_field
{

	public function &setValue($val)
	{
		$this->value = $val;

		return $this;
	}

	public function getHTML()
	{
		return '<input type="hidden" name="' . $this->name . '" value="' . htmlspecialchars($this->value) . '"' . $this->_custom_attr() . ' />';
	}
}

class formslib_text extends formslib_field
{
	protected $buttonlefttype, $buttonrighttype, $buttonlefthtml, $buttonrighthtml;

	public function getHTML()
	{
		$left = ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3 && isset($this->buttonlefttype));
		$right = ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3 && isset($this->buttonrighttype));

		$html = '';

		if ($left || $right)
		{
			$html .= '<div class="input-group">';
		}

		if ($left)
		{
			$html .= '<span class="input-group-'.$this->buttonlefttype.'">';
			$html .= $this->buttonlefthtml;
			$html .= '</span>'.CRLF;
		}

		$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . htmlspecialchars($this->name) . '" id="fld_' . htmlspecialchars($this->name) . '" value="' . htmlspecialchars($this->value) . '" />'.CRLF;

		if ($right)
		{
			$html .= '<span class="input-group-'.$this->buttonrighttype.'">';
			$html .= $this->buttonrighthtml;
			$html .= '</span>'.CRLF;
		}

		if ($left || $right)
		{
			$html .= '</div><!--/.input-group-->'.CRLF;
		}

		return $html;
	}

	public function getHTMLReadOnly()
	{
		return '<span name="' . htmlspecialchars($this->name) . '" id="fld_' . htmlspecialchars($this->name) . '" ' . $this->_custom_attr() . $this->_class_attr() . '><strong>' . htmlspecialchars($this->value) . '</strong></span>'; // TODO: Move strong to a class
	}

	public function &setInputGroupLeft($type, $html)
	{
		$this->buttonlefttype = $type;
		$this->buttonlefthtml = $html;

		return $this;
	}

	public function &setInputGroupRight($type, $html)
	{
		$this->buttonrighttype = $type;
		$this->buttonrighthtml = $html;

		return $this;
	}
}

/**
 * Abstract class for fields with options
 * e.g.
 * select, radio, etc.
 */
abstract class formslib_options extends formslib_field
{
	protected $options;

	public function &addOption($value, $label)
	{
		$this->options[$value] = $label;

		return $this;
	}

	public function &setOptions($in_opt)
	{
		if (! is_array($in_opt))
		{
			echo '<p>FORMSLIB ERROR: Options passed not an array, setting options for field: ' . htmlspecialchars($this->name) . '</p>';
			$this->options = array(
				'' => 'FORMSLIB ERROR: No options set'
			);
			return false;
		}
		$this->options = $in_opt;

		return $this;
	}

	public function getEmailValue()
	{
		if (isset($this->options[$this->value]))
			return $this->options[$this->value];
		else
			return '';
	}

	public function getDataDump()
	{
		$data = parent::getDataDump();

		$data['value'] = $this->options[$this->value];
		$data['rawvalue'] = $this->value;

		return $data;
	}

	public function &setOptionsRange($start, $end, $default = false)
	{
		$this->options = array();

		if ($default !== false)
		{
			$this->options[''] = $default;
		}

		if ($start < $end)
		{
			for ($seq = $start; $seq <= $end; $seq++)
			{
				$this->options[$seq] = $seq;
			}
		}
		else
		{
			for ($seq = $start; $seq >= $end; $seq--)
			{
				$this->options[$seq] = $seq;
			}
		}

		return $this;
	}

	public function validate($value)
	{
		$valid = parent::validate($value);

		if ($value !== null && $value != '' && !in_array($value, array_keys($this->options)))
		{
			$valid = false;
			$this->errorlist[] = array(
					'name' => $this->name,
					'label' => $this->label,
					'message' => 'A valid option for ' . $this->label . ' was not selected '
			);
		}

		return $valid;
	}

	public function &getObjectValue()
	{
		$value = $this->value;

		return $value;
	}
}

class formslib_radio extends formslib_options
{
	private $labelclass = array();
	protected $requireEquivalency = false;
	protected $ignoreNull = false;

	public function getHTML()
	{
		$disabled = (isset($this->attrib['disabled'])) ? true : false;

		$html = '';
		foreach ($this->options as $value => $label)
		{
			$id = $this->name . '__' . htmlspecialchars($value);

			if ($this->requireEquivalency)
			{
				$selected = ($this->value === $value) ? ' checked="checked"' : '';
			}
			elseif ($this->ignoreNull)
			{
				$selected = (!is_null($this->value) && $this->value == $value) ? ' checked="checked"' : '';
			}
			else
			{
				$selected = ($this->value == $value) ? ' checked="checked"' : '';
			}

			$labelclass = (count($this->labelclass)) ? ' ' . implode(' ', $this->labelclass) : '';

			$dis_str = ($disabled) ? ' disabled="disabled"' : '';

			$html .= '<label for="' . $id . '" class="formslib_label_radio' . $labelclass . '"><input type="radio" name="' . htmlspecialchars($this->name) . '" id="' . $id . '"' . $selected .$dis_str. ' value="' . htmlspecialchars($value) . '" />&nbsp;' . htmlspecialchars($label) . '</label> ';
		}

		return $html;
	}

	public function &addLabelClass($class)
	{
		$this->labelclass[] = $class;

		return $this;
	}

	public function &requireEquivalency($in = true)
	{
		$this->requireEquivalency = $in;

		return $this;
	}

	public function &ignoreNull($in = true)
	{
		$this->ignoreNull = $in;

		return $this;
	}
}

class formslib_select extends formslib_options
{

	public function getHTML()
	{
		$html = '';

		$html .= '<select' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '" id="fld_' . htmlspecialchars($this->name) . '">' . CRLF;

		foreach ($this->options as $value => $label)
		{
			$html .= '<option value="' . htmlentities($value) . '"';
			if ($this->value == $value) $html .= ' selected="selected"';
			$html .= '>';
			$html .= htmlentities($label);
			$html .= '</option>' . CRLF;
		}

		$html .= '</select>' . CRLF;

		return $html;
	}

	public function getHTMLReadOnly()
	{
		$label = (isset($this->options[$this->value])) ? $this->options[$this->value] : '- Unknown value -';

		return '<span name="' . htmlspecialchars($this->name) . '" id="fld_' . htmlspecialchars($this->name) . '" ' . $this->_custom_attr() . $this->_class_attr() . '><strong>' . htmlspecialchars($label) . '</strong></span>'; // TODO: Move strong to a class
	}
}

class formslib_checkbox extends formslib_field
{
	protected $checkedvalue = 'checked';
	private $tickbefore = false;
	private $truetext = 'Yes';
	private $falsetext = 'No';
	private $rawboxonly = false;

	public function &setTickBefore()
	{
		$this->tickbefore = true;

		return $this;
	}

	public function &setTickAfter()
	{
		$this->tickbefore = false;

		return $this;
	}

	public function getHTML()
	{
		$html = '';

		$checked = ($this->value == $this->checkedvalue) ? ' checked="checked"' : '';

		$text = htmlspecialchars($this->label) . CRLF;
		$input = '<input type="checkbox" value="' . $this->checkedvalue . '"' . $checked . ' ' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '" id="fld_' . htmlspecialchars($this->name) . '" />' . CRLF;

		if ($this->rawboxonly)
		{
			$html .= $input;
		}
		else
		{
			$html .= '<label for="fld_' . $this->name . '" class="formslib_label_checkbox">' . CRLF;
			$html .= ($this->tickbefore) ? $input . $text : $text . $input;
			$html .= '</label>';
		}

		return $html;
	}

	public function display(formslib_form &$form)
	{
		$outputstyle = $form->outputstyle;

		$mand = ($this->mandatory) ? $form->mandatoryHTML : '';

		if (! $this->rawoutput)
		{
			switch ($outputstyle)
			{
				case FORMSLIB_STYLE_P:
					echo $this->htmlbefore;
					echo '<p>' . CRLF;
					// echo '<label for="'.$this->name.'">'.htmlspecialchars($this->label).'</label> '.CRLF;
					echo $this->getHTML() . CRLF;
					echo $mand;
					echo '</p>' . CRLF . CRLF;
					echo $this->htmlafter;
					break;

				case FORMSLIB_STYLE_DL:
				default:
					echo $this->htmlbefore;
					echo '<dl>' . CRLF;
					echo '<dt><label for="' . $this->name . '">' . htmlspecialchars($this->label) . '</label></dt>' . CRLF;
					echo '<dd>' . $this->getHTML() . $mand . '</dd>' . CRLF;
					echo '</dl>' . CRLF . CRLF;
					echo $this->htmlafter;
					break;

				case FORMSLIB_STYLE_BOOTSTRAP:
					echo $this->htmlbefore;
					echo $this->getHTML() . CRLF;
					echo $this->htmlafter;
					break;

				case FORMSLIB_STYLE_BOOTSTRAP3: //TODO: Review from upstream
				    echo $this->htmlbefore;
				    echo '<div>'.CRLF;
				    echo $this->getHTML() . CRLF;
				    echo '</div>'.CRLF;
				    echo $this->htmlafter;
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

	public function &setTrueText($text)
	{
		$this->truetext = $text;

		return $this;
	}

	public function &setFalseText($text)
	{
		$this->falsetext = $text;

		return $this;
	}

	public function getEmailValue()
	{
		return ($this->value == $this->checkedvalue) ? $this->truetext : $this->falsetext;
	}

	public function getDataDump()
	{
		$data = parent::getDataDump();

		$data['value'] = $this->value;
		$data['checkedvalue'] = $this->checkedvalue;

		return $data;
	}

	public function &setRawBoxOnly()
	{
		$this->rawboxonly = true;

		return $this;
	}

	public function &setCheckedValue($checkedvalue)
	{
		$this->checkedvalue = $checkedvalue;

		return $this;
	}

	public function getHTMLReadOnly()
	{
		// TODO: Complete
		$html = '';

		$ids = 'name="' . $this->name . '" id="fld_' . htmlspecialchars($this->name) . '"';
		$checked = ($this->value == $this->checkedvalue) ? '<span ' . $ids . ' class="colour-positive">&#10004;</span>' : '<span ' . $ids . ' class="colour-negative">&#10008;</span>';

		$text = htmlspecialchars($this->label) . CRLF;

		if ($this->rawboxonly)
		{
			$html .= $checked;
		}
		else
		{
			$html .= '<label for="fld_' . $this->name . '" class="formslib_label_checkbox">' . CRLF;
			$html .= ($this->tickbefore) ? $checked . $text : $text . $checked;
			$html .= '</label>';
		}

		return $html;
	}

	public function &getObjectValue()
	{
		$value = ($this->value == $this->checkedvalue) ? true : false;

		return $value;
	}
}

class formslib_password extends formslib_text
{

	public function getHTML()
	{
		return '<input type="password"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '" id="fld_' . htmlspecialchars($this->name) . '" value="" autocomplete="off" />';
	}
}

class formslib_email extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('regex', '/^' . EMAIL_VALIDATE . '$/i', 'Invalid email address format');
	}
}

class formslib_phone extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('regex', '|^[0-9\+ ]+$|i', 'Phone number may only contain numbers, spaces, and the plus symbol.');
	}
}

class formslib_postalcode extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('maxlength', 12, 'Postal codes must be 12 characters or less');
		$this->addRule('regex', '/^[a-z0-9\s-]*$/i', 'Please enter the postal code using only letters, numbers, dashes and spaces');
	}
}

class formslib_postcode extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('regex', '/^' . POSTCODE_VALIDATE . '$/i', 'The postcode you entered has not been recognised as a full UK postcode');
	}
}

class formslib_textarea extends formslib_field
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addAttr('rows', '6'); // Default rows
		$this->addAttr('cols', '30'); // Default cols
	}

	public function getHTML()
	{
		return '<textarea id="fld_' . $this->name . '" name="' . $this->name . '"' . $this->_custom_attr() . $this->_class_attr() . '>' . htmlspecialchars($this->value) . '</textarea>';
	}

	function getHTMLReadOnly()
	{
		return '<span name="' . htmlspecialchars($this->name) . '" id="fld_' . htmlspecialchars($this->name) . '" ' . $this->_custom_attr() . $this->_class_attr() . '><strong>' . Formslib::convertTextToHtml($this->value) . '</strong></span>'; // TODO: Move strong to a class
	}
}

class formslib_url extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('regex', '`' . VALIDATE_URL . '`i', 'Invalid URL format');
	}
}

class formslib_integer extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('regex', '|^-?[0-9]+$|i', 'Not a whole number');
	}
}

class formslib_number extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('regex', '|^-?[0-9.]+$|i', 'Not a number');
	}
}

class formslib_yesno extends formslib_radio
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addOption('1', 'Yes');
		$this->addOption('0', 'No');
	}
}

class formslib_confirmtick extends formslib_checkbox
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->setRawOutput();
		$this->setMandatory();
	}
}

class formslib_sqldate extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('sqldate', '', 'Invalid date (must be yyyy-mm-dd)');
	}
}

class formslib_xml extends formslib_textarea
{
}

class formslib_file extends formslib_field
{

	public function getHTML()
	{
		return '<input type="file"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '" value="' . htmlspecialchars($this->value) . '" />';
	}
}

class formslib_multiselect extends formslib_select
{

	public function getHTML()
	{
		$html = '';

		$html .= '<select multiple="multiple"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '[]">' . CRLF;

		foreach ($this->options as $value => $label)
		{

			$html .= '<option value="' . htmlspecialchars($value) . '"';
			// if ($this->value == $value)
			// $html .= ' selected="selected"';
			if (is_array($this->value))
			{
				if (in_array($value, $this->value))
				{
					$html .= ' selected="selected"';
				}
			}
			$html .= '>';
			$html .= htmlspecialchars($label);
			$html .= '</option>' . CRLF;
		}

		$html .= '</select>' . CRLF;

		return $html;
	}

	public function getEmailValue()
	{
		if (is_array($this->value))
		{
			// if (isset($this->options[$this->value]))
			return implode(';', $this->value);
		}
		else
			return '';
	}
}

class formslib_cardnumber extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('maxlength', 19, 'Card number must only contain digits and be between 15 and 19 digits long');
		$this->addRule('regex', '|^[0-9{15,19}]+$|i', 'Card number must only contain digits and be between 15 and 19 digits long');
	}
}

class formslib_cardmonthyear extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('maxlength', 4, 'Must be a valid date in the format MMYY');
		$this->addRule('regex', '/^(0[1-9]|1[0-2])[0-2][0-9]$/', 'Must be a valid date in the format MMYY');
	}
}

class formslib_cardcvc extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('maxlength', 3, 'Must be a three digit number');
		$this->addRule('regex', '/^[0-9]{3}$/', 'Must be a three digit number');
	}
}

class formslib_personname extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('regex', '/^[a-z\s\-\']*$/i', 'Please make sure you only enter letters, hyphen or apostrophes');
	}
}

abstract class formslib_composite extends formslib_field
{
	protected $composites = array();
	public $composite_values = array();

	protected function _set_composites($composites)
	{
		$this->composites = $composites;

		// TODO: Review the need for this
		foreach ($this->composites as $key)
			$this->composite_values[$key] = '';
	}

	public function get_composites()
	{
		return $this->composites;
	}

	protected function _class_attr($extraclass = '')
	{
		$class_str = '';
		$classes = $this->classes;
		$classes[] = $extraclass;

		$class_str .= ' class="';
		$first = true;
		foreach ($classes as $classname)
		{
			if (! $first) $class_str .= ' ';
			$class_str .= $classname;
			$first = false;
		}
		$class_str .= '"';

		return $class_str;
	}

	public function getEmailValue()
	{
		return '[Composite field function getEmailValue() not overwritten]';
	}

	public function &getObjectValue()
	{
		throw new \Exception('Composite field function getObjectValue() not overwritten for field type '.get_class($this));
	}
}

class formslib_date extends formslib_composite
{
	var $startyear, $endyear;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->_set_composites(array(
			'day',
			'month',
			'year'
		));

		$this->startyear = date('Y');
		$this->endyear = date('Y');

		$this->addRule('composite_date_exists', '', 'The date entered was incomplete or does not exist');
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

		// Day
		$html .= '<select' . $this->_custom_attr() . $this->_class_attr('formslib_date__day'.$classes) . ' name="' . $this->name . '__day">' . CRLF;
		$html .= '<option value="0">Day</option>';
		for ($i = 1; $i <= 31; $i++)
		{
			$html .= '<option value="' . $i . '"';
			if ($this->composite_values['day'] == $i) $html .= ' selected="selected"';
			$html .= '>' . $i . '</option>' . CRLF;
		}
		$html .= '</select>' . CRLF;

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-xs-4">' . CRLF;
			$classes = '';
		}
		else
		{
			$classes = ' input-small col-sm-4';
		}

		// Month
		$html .= '<select' . $this->_custom_attr() . $this->_class_attr('formslib_date__month'.$classes) . ' name="' . $this->name . '__month">' . CRLF;
		for ($i = 0; $i <= 12; $i++)
		{
			$html .= '<option value="' . $i . '"';
			if ($this->composite_values['month'] == $i) $html .= ' selected="selected"';
			$html .= '>' . $GLOBALS['mn'][$i] . '</option>' . CRLF; // TODO: Review use of this lookup here
		}
		$html .= '</select>' . CRLF;

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-xs-4">' . CRLF;
			$classes = '';
		}
		else
		{
			$classes = ' input-small col-sm-4';
		}

		// Year
		$html .= '<select' . $this->_custom_attr() . $this->_class_attr('formslib_date__year'.$classes) . ' name="' . $this->name . '__year">' . CRLF;
		$html .= '<option value="0">Year</option>';
		for ($i = $this->startyear; $i <= $this->endyear; $i++)
		{
			$html .= '<option value="' . $i . '"';
			if ($this->composite_values['year'] == $i) $html .= ' selected="selected"';
			$html .= '>' . $i . '</option>' . CRLF;
		}
		$html .= '</select>' . CRLF;

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div><!-- /.col-xs-4 -->' . CRLF;
			$html .= '</div><!-- /.row -->' . CRLF;
		}

		return $html;
	}

	public function &set_years($start, $end)
	{
		$this->startyear = $start;
		$this->endyear = $end;

		return $this;
	}

	public function getEmailValue()
	{
		return $this->composite_values['year'] . '-' . sprintf('%02d', $this->composite_values['month']) . '-' . sprintf('%02d', $this->composite_values['day']);
	}

	public function &getObjectValue()
	{
		$date = null;

		if ($this->composite_values['year'] != 0 && $this->composite_values['month'] != 0 && $this->composite_values['day'] != 0)
		{
			$date = \DateTime::createFromFormat('Y-m-d H:i:s', $this->getEmailValue().' 00:00:00');
		}

		return $date;
	}
}

class formslib_ukbankacct extends formslib_text
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->addRule('maxlength', 8, 'Bank account number must only contain digits and be between 7 and 8 digits long');
		$this->addRule('regex', '|^[0-9]{7,8}$|i', 'Bank account number must only contain digits and be between 7 and 8 digits long');
	}
}

class formslib_uksortcode extends formslib_composite
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->_set_composites(array(
			'1',
			'2',
			'3'
		));
		$this->addRule('compsite_sortcode', '', 'Each part of your sort code must contain 2 digits');
		$this->addAttr('maxlength', '2');
		$this->addAttr('size', '2'); // Text box width, in characters
	}

	public function getHTML()
	{
		$html = '';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '<div class="row">' . CRLF;
			$html .= '<div class="col-xs-4">' . CRLF;
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr('formslib__uksortcode1') . ' name="' . $this->name . '__1" value="' . htmlspecialchars($this->composite_values['1']) . '" />';
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-xs-4">' . CRLF;
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr('formslib__uksortcode2') . ' name="' . $this->name . '__2" value="' . htmlspecialchars($this->composite_values['2']) . '" />';
// 			$html .= '<div class="input-group"><span class="input-group-addon">-</span><input type="text"' . $this->_custom_attr() . $this->_class_attr('formslib__uksortcode2') . ' name="' . $this->name . '__2" value="' . htmlspecialchars($this->composite_values['2']) . '" /></div>';
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-xs-4">' . CRLF;
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr('formslib__uksortcode3') . ' name="' . $this->name . '__3" value="' . htmlspecialchars($this->composite_values['3']) . '" />';
// 			$html .= '<div class="input-group"><span class="input-group-addon">-</span><input type="text"' . $this->_custom_attr() . $this->_class_attr('formslib__uksortcode3') . ' name="' . $this->name . '__3" value="' . htmlspecialchars($this->composite_values['3']) . '" /></div>';
			$html .= '</div><!-- /.col-xs-4 -->' . CRLF;
			$html .= '</div><!-- /.row -->' . CRLF;
		}
		else
		{
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr('formslib__uksortcode1') . ' name="' . $this->name . '__1" value="' . htmlspecialchars($this->composite_values['1']) . '" /> - ';
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr('formslib__uksortcode2') . ' name="' . $this->name . '__2" value="' . htmlspecialchars($this->composite_values['2']) . '" /> - ';
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr('formslib__uksortcode3') . ' name="' . $this->name . '__3" value="' . htmlspecialchars($this->composite_values['3']) . '" />';
		}

		return $html;
	}
}

class formslib_ticklist extends formslib_composite
{
	private $ticklist = array();
	private $checkedvalue = 'checked';
	private $delimiter = "\n";

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
		// TODO: Inline CSS, get this into a proper style sheet!
		$html = '<span class="formslib_ticklist_container" style="display: block; float: left;">';

		foreach ($this->ticklist as $index => $label)
		{
			$checked = ($this->composite_values[$index] == $this->checkedvalue) ? ' checked="checked"' : '';

			$text = htmlspecialchars($label) . CRLF;
			$input = '<input type="checkbox" value="' . $this->checkedvalue . '"' . $checked . ' ' . $this->_custom_attr() . $this->_class_attr('formslib_ticklist') . ' name="' . htmlspecialchars($this->name . '__' . $index) . '" id="fld_' . htmlspecialchars($this->name . '__' . $index) . '" title="' . htmlspecialchars($label) . '" />' . CRLF;

			// TODO: When fixing the CSS, re-establish the labels here
			// $html .= '<label for="'.$this->name.'__'.$index.'" class="formslib_label_checkbox">'.CRLF;
			$html .= $input . $text . '<br />';
			// $html .= '</label><br />';
		}

		$html .= '</span>';

		$html .= '<span style="display: block; clear: both;"></span>';

		return $html;
	}

	public function getHTMLReadOnly()
	{
		$html = '<span class="formslib_ticklist_container" style="display: block; float: left;">';

		foreach ($this->ticklist as $index => $label)
		{
			$ids = 'name="' . $this->name . '" id="fld_' . htmlspecialchars($this->name) . '"';
			$checked = ($this->composite_values[$index] == $this->checkedvalue) ? '<span ' . $ids . ' class="colour-positive">&#10004;</span>' : '<span ' . $ids . ' class="colour-negative">&#10008;</span>';

			$text = htmlspecialchars($label) . CRLF;
			// $input = '<input type="checkbox" value="'.$this->checkedvalue.'"'.$checked.' '.$this->_custom_attr().$this->_class_attr('formslib_ticklist').' name="'.htmlspecialchars($this->name.'__'.$index).'" id="fld_'.htmlspecialchars($this->name.'__'.$index).'" title="'.htmlspecialchars($label).'" />'.CRLF;

			// TODO: When fixing the CSS, re-establish the labels here
			$html .= $checked . $text . '<br />' . CRLF;
		}

		$html .= '</span>';

		$html .= '<span style="display: block; clear: both;"></span>';

		return $html;
	}

	public function getEmailValue()
	{
		$checked_vals = array();
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
		$checked = array();

		foreach ($this->composites as $value)
		{
			if (isset($this->composite_values[$value]) && $this->composite_values[$value] == $this->checkedvalue) $checked[$value] = $this->ticklist[$value];
		}

		return $checked;
	}
}

class formslib_carddate extends formslib_composite
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->_set_composites(array(
			'month',
			'year'
		));
	}

	public function getHTML()
	{
		$html = '';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '<div class="row">' . CRLF;
			$html .= '<div class="col-xs-3">' . CRLF;
		}

		// Month
		$html .= '<select' . $this->_custom_attr() . $this->_class_attr('formslib_date__month') . ' name="' . $this->name . '__month">' . CRLF;
		$html .= '<option value="">Month</option>';
		for ($i = 1; $i <= 12; $i++)
		{
			$html .= '<option value="' . $i . '"';
			if ($this->composite_values['month'] == $i) $html .= ' selected="selected"';
			$html .= '>' . sprintf('%02d', $i) . '</option>' . CRLF;
		}
		$html .= '</select>' . CRLF;

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-xs-3">' . CRLF;
		}

		// Year
		$html .= '<select' . $this->_custom_attr() . $this->_class_attr('formslib_date__year') . ' name="' . $this->name . '__year">' . CRLF;
		$html .= '<option value="0">Year</option>';

		for ($i = $this->startyear; $i <= $this->endyear; $i++)
		{
			$html .= '<option value="' . $i . '"';

			if ($this->composite_values['year'] == $i) $html .= ' selected="selected"';

			$html .= '>' . $i . '</option>' . CRLF;
		}

		$html .= '</select>' . CRLF;

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div>' . CRLF;
			$html .= '</div><!--/.row-->'.CRLF;
		}

		return $html;
	}

	public function &setYear($year)
	{
		if ($year == 'start')
		{
			$this->startyear = date('Y', strtotime('-5 years'));

			$this->endyear = date('Y');
		}

		if ($year == 'end')
		{
			$this->startyear = date('Y');
			$this->endyear = date('Y', strtotime('+5 years'));
		}

		return $this;
	}
}

class formslib_wysiwyg_light extends formslib_textarea
{
	private $button_list;

	public function __construct($name)
	{
		parent::__construct($name);

		$this->button_list = array('bold','italic','ol','ul','fontFormat','removeformat','xhtml');
	}

	public static function getHeader(&$class)
	{
		$class = __CLASS__;

		if (!defined('CONFIG_PATH_NICEDIT'))
			define('CONFIG_PATH_NICEDIT', '/phplib/nicEdit-latest.js');

			return '<script type="text/javascript" src="'.CONFIG_PATH_NICEDIT.'"></script>'; // TODO: Alter URL
	}

	public function getHTML()
	{
		$buttonlist = implode('\',\'', $this->button_list);

		$html = <<<EOF
<script type="text/javascript">
<!--
bkLib.onDomLoaded(function() {
	new nicEditor({buttonList: ['$buttonlist']}).panelInstance('fld_{$this->name}');
});
//-->
</script>
EOF;

		$html .= parent::getHTML();

		return $html;
	}

	public function &setButtons(array $buttons)
	{
		$this->button_list = $buttons;

		return $this;
	}
}

class formslib_toggle_button extends formslib_checkbox
{
	protected $btnclass = '';
	protected $button_text = '';

	public function getHTML()
	{
		$html = '';

		$checked = ($this->value == $this->checkedvalue) ? ' checked="checked"' : '';
		$active = ($this->value == $this->checkedvalue) ? ' active' : '';

		$html .= '<div class="btn-group" data-toggle="buttons">' . CRLF;
		$html .= '<label for="fld_' . $this->name . '" class="btn' . $this->btnclass . $active . '">' . CRLF;
		$html .= '<input type="checkbox" value="' . $this->checkedvalue . '"' . $checked . ' ' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '" id="fld_' . htmlspecialchars($this->name) . '" title="' . htmlentities($this->label) . '" />' . CRLF;
		$html .= htmlspecialchars($this->button_text);
		$html .= '</label>' . CRLF;
		$html .= '</div>' . CRLF;

		return $html;
	}

	public function &addButtonClass($class)
	{
		$this->btnclass .= ' ' . $class;

		return $this;
	}

	public function &setButtonText($text)
	{
		$this->button_text = $text;

		return $this;
	}
}

class formslib_time extends formslib_composite
{

	public function __construct($name)
	{
		parent::__construct($name);
		$this->_set_composites(array(
				'hour',
				'minute',
		));

	}

	public function getHTML()
	{
		$html = '';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '<div class="row">' . CRLF;
			$html .= '<div class="col-xs-4">' . CRLF;
		}

		// Hour
		$html .= '<select' . $this->_custom_attr() . $this->_class_attr('formslib_date__hour input-mini') . ' name="' . $this->name . '__hour">' . CRLF;
		$html .= '<option value="">Hour</option>';

		for ($i = 0; $i <= 23; $i++)
		{
			if($i == 0)
			{
				$html .= '<option value="00"';
				if ($this->composite_values['hour'] == $i) $html .= ' selected="selected"';
				$html .= '>00</option>' . CRLF;


			}
			elseif ($i <10)
			{
				$html .= '<option value="0' . $i . '"';
				if ($this->composite_values['hour'] == $i) $html .= ' selected="selected"';
				$html .= '>0' . $i . '</option>' . CRLF;
			}
			else
			{
				$html .= '<option value="' . $i . '"';
				if ($this->composite_values['hour'] == $i) $html .= ' selected="selected"';
				$html .= '>' . $i . '</option>' . CRLF;
			}
		}
		$html .= '</select>' . CRLF;

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-xs-4">' . CRLF;
		}

		// Minute
		$html .= '<select' . $this->_custom_attr() . $this->_class_attr('formslib_date__minute input-small col-sm-4') . ' name="' . $this->name . '__minute">' . CRLF;
		$html .= '<option value="">Minute</option>';
		for ($i = 0; $i <= 59; $i++)
		{
			if($i == 0)
			{
				$html .= '<option value="00"';
				if ($this->composite_values['minute'] == $i) $html .= ' selected="selected"';
				$html .= '>00</option>' . CRLF;
			}
			elseif($i < 10)
			{
				$html .= '<option value="0' . $i . '"';
				if ($this->composite_values['minute'] == $i) $html .= ' selected="selected"';
				$html .= '>0' . $i . '</option>' . CRLF;
			}
			else
			{
				$html .= '<option value="' . $i . '"';
				if ($this->composite_values['minute'] == $i) $html .= ' selected="selected"';
				$html .= '>' . $i . '</option>' . CRLF;
			}


		}
		$html .= '</select>' . CRLF;

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-xs-4">' . CRLF;
		}

		$html .= '</select>' . CRLF;

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '</div><!-- /.col-xs-4 -->' . CRLF;
			$html .= '</div><!-- /.row -->' . CRLF;
		}

		return $html;
	}

	public function getEmailValue()
	{
		return sprintf('%02d',$this->composite_values['hour']) . ':' . sprintf('%02d', $this->composite_values['minute']);
	}
}

class formslib_datepicker extends formslib_text
{
    protected $startyear, $endyear;
    protected $startdate, $enddate;

    public function __construct($name)
    {
        parent::__construct($name);

        $this->startyear = date('Y');
        $this->endyear = date('Y');

        $this->addRule('date_format', 'uk', 'Dates must be in the format dd/mm/yyyy');
        $this->addRule('date_exists', 'uk', 'The date entered was incomplete or does not exist');
	}

    public function &set_years($start, $end)
    {
        $this->startyear = $start;
        $this->endyear = $end;

        $this->addRule('Dates_UkAfter', '01/01/'.$start, 'Must be after 01/01/'.$start);
        $this->addRule('Dates_UkBefore', '31/12/'.$end, 'Must be before 31/12/'.$end);

        return $this;
    }

    public function getHTML()
    {
        $html = '<div class="input-group date">';
        $html .= '<input type="text" '.$this->_custom_attr().$this->_class_attr('form-control').' name="'.htmlspecialchars($this->name).'" id="fld_'.htmlspecialchars($this->name).'" value="'.htmlspecialchars($this->value).'">';
        $html .= '<span class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></span>';
        $html .= '</div>';

        echo $this->_generateDatepickerJS(); //TODO: Append JS snippet to form

        return $html;
    }

    protected function _generateDatepickerJS()
    {
        $id = $this->name; //TODO: Properly escape

        if (isset($this->startdate))
        {
            $start = ', startDate: "'.$this->startdate.'"';
        }
        elseif (isset($this->startyear))
        {
            $start = ', startDate: "01/01/'.$this->startyear.'"';
        }
        else
        {
			$start = '';
        }

        if (isset($this->enddate))
        {
			$end = ', endDate: "'.$this->enddate.'"';
        }
        elseif (isset($this->endyear))
        {
                    $end = ', endDate: "31/12/'.$this->endyear.'"';
        }
        else
        {
			$end = '';
    	}

		return <<<EOF
<script type="text/javascript">
$(document).ready(function()
{
    $('input#fld_$id').parent().datepicker({
        weekStart: 1,
        language: "en-GB",
        format: 'dd/mm/yyyy',
        autoclose: true
        $start
        $end
    });
});
</script>
EOF;

    }

    public function &setStartDate($date)
    {
        $this->startdate = $date;

        $this->addRule('Dates_UkAfter', $date, 'Must be on or after '.$date);

        return $this;
    }

    public function &setEndDate($date)
    {
        $this->enddate = $date;

        $this->addRule('Dates_UkBefore', $date, 'Must be on or before '.$date);

        return $this;
    }

    public function &getObjectValue()
    {
    	$date = null;

    	if ($this->value != '') $date = Formslib::getUkDate($this->value);

    	return $date;
    }
}

class formslib_dateselecttime extends formslib_composite
{
	protected $startDate, $endDate, $time;
	protected $field_date;
	protected $dateformat;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->_set_composites(array(
			'date',
			'time'
		));

		$this->field_date = new formslib_select($name.'__date');

		$this->addRule('composite_timerangeformat', null, 'Times must be in the 24-hour format hh:mm');
	}

	public function &setDateRange($startDate, $endDate)
	{
		$patternDate = '/[0-9]{4}-[0-9]{2}-[0-9]{2}/';
		if (!preg_match($patternDate, $startDate) || !preg_match($patternDate, $endDate)) throw new Exception('Invalid date format');

		if ($startDate > $endDate) throw new Exception('End date constraint before start date');

		$start = new DateTime($startDate);
		$end = new DateTime($endDate);

		if (version_compare(PHP_VERSION, '5.4.0', '>='))
		{
			$interval = $start->diff($end);
			$daycount = $interval->days;
		}
		else
		{
			$daycount = abs(strtotime($endDate) - strtotime($startDate)) / (60*60*24);
		}

		if ($daycount < 5) $this->dateformat = 'l';
		elseif ($daycount < 21) $this->dateformat = 'l jS';
		elseif ($daycount >= 21) $this->dateformat = 'l j M';

		$days = array();
		for ($i = 0; $i <= $daycount; $i++)
		{
			$start = new DateTime($startDate);
			$date = $start->add(new DateInterval('P'.$i.'D'));
			$key = $date->format('Y-m-d');
			$days[$key] = $date->format($this->dateformat);
		}

		$this->field_date->setOptions(array('' => '- Select day -')+$days);

		return $this;
	}

	protected function _prepareOutput()
	{
		$this->field_date->value = $this->composite_values['date'];
		$this->field_date->setClasses($this->getClasses());
	}

	public function getHTML()
	{
		$this->_prepareOutput();

		$html = '';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '<div class="row">' . CRLF;
			$html .= '<div class="col-sm-6">' . CRLF;
			$html .= $this->field_date->getHTML();
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-sm-3">' . CRLF;
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '__time" value="' . htmlspecialchars($this->composite_values['time']) . '" />';
			$html .= '</div><!-- /.col-sm-3 -->' . CRLF;
			$html .= '</div><!-- /.row -->' . CRLF;
		}
		else
		{
			$html .= $this->field_date->getHTML().' - ';
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '__time" value="' . htmlspecialchars($this->composite_values['time']) . '" />';
		}

		return $html;
	}

	public function getEmailValue()
	{
		return date($this->dateformat, strtotime($this->composite_values['date'])).' '.$this->composite_values['time'];
	}

	public function &getObjectValue()
	{
		$date = \DateTime::createFromFormat('!d/m/Y H:i', $this->composite_values['date'].' '.$this->composite_values['time']);

		return $date;
	}
}

class formslib_dateselecttimerange extends formslib_dateselecttime
{
	private $startTime, $endTime;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->_set_composites(array(
				'date',
				'start',
				'end'
		));

		$this->addRule('composite_timerangeorder', null, 'End time must be after start time');
	}

	public function &setTimeRange($startTime, $endTime)
	{
		$patternTime = '/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/';
		if (!preg_match($patternTime, $startTime) || !preg_match($patternTime, $endTime)) throw new Exception('Invalid time format');

		if (str_replace($endTime, ':', '') <= str_replace($startTime, ':', '')) throw new Exception('End time constraint before start time');

		$this->startTime = $startTime;
		$this->endTime = $endTime;

		return $this;
	}

	public function getHTML()
	{
		$this->_prepareOutput();

		$html = '';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '<div class="row">' . CRLF;
			$html .= '<div class="col-sm-6">' . CRLF;
			$html .= $this->field_date->getHTML();
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-sm-3">' . CRLF;
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '__start" value="' . htmlspecialchars($this->composite_values['start']) . '" />';
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-sm-3">' . CRLF;
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '__end" value="' . htmlspecialchars($this->composite_values['end']) . '" />';
			$html .= '</div><!-- /.col-sm-3 -->' . CRLF;
			$html .= '</div><!-- /.row -->' . CRLF;
		}
		else
		{
			$html .= $this->field_date->getHTML().' - ';
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '__start" value="' . htmlspecialchars($this->composite_values['start']) . '" /> - ';
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '__end" value="' . htmlspecialchars($this->composite_values['end']) . '" />';
		}

		return $html;
	}

	public function getEmailValue()
	{
		return date($this->dateformat, strtotime($this->composite_values['date'])).' '.$this->composite_values['start'].'-'.$this->composite_values['end'];
	}
}

class formslib_datepickertime extends formslib_composite
{
	protected $startyear, $endyear;
	protected $startdate, $enddate;
	protected $time;
	protected $field_date;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->_set_composites(array(
				'date',
				'time'
		));

		$this->field_date = new formslib_datepicker($name.'__date');

		$this->addRule('composite_timerangeformat', null, 'Times must be in the 24-hour format hh:mm');
	}

	public function &set_years($start, $end)
	{
		$this->startyear = $start;
		$this->endyear = $end;

		$this->field_date->set_years($start, $end);

		return $this;
	}

	public function &setStartDate($date)
	{
		$this->startdate = $date;

		$this->field_date->setStartDate($date);

		return $this;
	}

	public function &setEndDate($date)
	{
		$this->enddate = $date;

		$this->field_date->setEndDate($date);

		return $this;
	}

	protected function _prepareOutput()
	{
		$this->field_date->value = $this->composite_values['date'];
		$this->field_date->setClasses($this->getClasses());
		$this->field_date->setAttributes($this->getAttributes());
	}

	public function getHTML()
	{
		$this->_prepareOutput();

		$html = '';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			$html .= '<div class="row">' . CRLF;
			$html .= '<div class="col-sm-9">' . CRLF;
			$html .= $this->field_date->getHTML();
			$html .= '</div>' . CRLF;
			$html .= '<div class="col-sm-3">' . CRLF;
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '__time" value="' . htmlspecialchars($this->composite_values['time']) . '" />';
			$html .= '</div><!-- /.col-sm-3 -->' . CRLF;
			$html .= '</div><!-- /.row -->' . CRLF;
		}
		else
		{
			$html .= $this->field_date->getHTML().' - ';
			$html .= '<input type="text"' . $this->_custom_attr() . $this->_class_attr() . ' name="' . $this->name . '__time" value="' . htmlspecialchars($this->composite_values['time']) . '" />';
		}

		return $html;
	}

	public function getEmailValue()
	{
		return date($this->dateformat, strtotime($this->composite_values['date'])).' '.$this->composite_values['time'];
	}

	public function &getObjectValue()
	{
		if ($this->composite_values['date'] == '' || $this->composite_values['time'] == '') return null;

		$date = \DateTime::createFromFormat('!d/m/Y H:i', $this->composite_values['date'].' '.$this->composite_values['time']);

		return $date;
	}
}