<?php
namespace formslib;

use formslib\Utility\Security;

class Form
{
	private $name, $id, $action, $method;

	/** @var \formslib_field[] */
	public $fields = [];

	/** @var Fieldset[] */
	public $fieldsets = [];

	public $outputstyle;
	public $submitlabel;
	public $mandatoryHTML, $semimandatoryHTML;
	public $optionalHTML = ' <small class="formslib_optional">(optional)</small>';

	private $errorlist = [];
	private $htmltop, $htmlbottom, $htmlbeforesubmit;
	private $classes = [];
	private $attrib = [];
	private $submitfieldset = false;
	private $nosubmitbutton = false;
	private $jqueryvalidate = false;
	private $obfuscate_js = false;
	private $customjs;
	private $types_used = [];
	private $submitclass = ['btn', 'btn-primary'];
	private $submit_grid_ratio = null;
	private $resultClass = '\formslib\Result\ResultObject';
	private $doubleClickTimeout = null;
	private $fsorder = [];
	private $optionalLabels = false;

	public function __construct($name)
	{
		$this->name = $name;
		$this->method = FORMSLIB_METHOD_POST;
		$this->outputstyle = FORMSLIB_STYLE_DL;
		$this->action = '?';
		$this->submitlabel = 'Submit';
	}

	public function setID($id)
	{
		$this->id = $id;
	}

	public function setAction($action)
	{
		$this->action = $action;
	}

	public function setMethod($method)
	{
		$this->method = $method;
	}

	public function setOutputStyle($style)
	{
		$this->outputstyle = $style;
	}

	public function setSubmitLabel($label)
	{
		$this->submitlabel = $label;
	}

	/**
	 * Create a new field in the form
	 *
	 * @param string $type
	 *        	Type of the field
	 * @param string $name
	 *        	Name of the field
	 * @param integer $error
	 *        	Pointer for error code
	 * @return \formslib_field_paramset
	 */
	public function &addField($type, $name)
	{
	    if ($type == 'field') throw new \Exception('FORMSLIB ERROR: Cannot add a field of abstract type "field": ' . $name);

		if (isset($this->fields[$name])) throw new \Exception('FORMSLIB ERROR: Duplicate field name: ' . $name);

		if (substr($type, 0, 1) == '\\')
		{
			$classnamespace = $type;
		}
		else
		{
			$classnamespace = 'formslib\Field\\'.$type;
			$classname = 'formslib_' . $type;
		}

		if (class_exists($classnamespace))
		{
			/** @var formslib_field $field */
			$field = new $classnamespace($name);
			if (!is_object($field)) throw new \Exception('FORMSLIB ERROR: Failed to create field object for: ' . $name);

			$this->fields[$name] = &$field;
		}
		elseif (class_exists($classname))
		{
			/** @var formslib_field $field */
			$field = new $classname($name);
			if (is_object($field))
			{
				$this->fields[$name] = &$field;
			}
			else
			{
                throw new \Exception('FORMSLIB ERROR: Failed to create field object for: ' . $name);
			}
		}
		else
		{
			throw new \Exception('FORMSLIB ERROR: No such field type "' . $type . '" for field name "' . $name . '"');
		}

		if (! in_array($type, $this->types_used)) $this->types_used[] = $type;

		return $field;
	}

	public function attachField(\formslib_field &$field)
	{
        $name = $field->getName();

        if (isset($this->fields[$name])) throw new \Exception('FORMSLIB ERROR: Duplicate field name: ' . $name);

        $this->fields[$name] = &$field;

        $type = $field->getType();

	    if (! in_array($type, $this->types_used)) $this->types_used[] = $type;
	}

	public function &addFieldSet($name)
	{
		if (isset($this->fieldsets[$name])) throw new \Exception('FORMSLIB ERROR: Duplicate fieldset name');

		$fieldset = new Fieldset($name);
		if (is_object($fieldset))
		{
			$this->fieldsets[$name] = &$fieldset;
			$this->fsorder[] = $name;
		}
		else
		{
			return false;
		}

		return $fieldset;
	}

	/**
	 * Set the values to be output when the form is displayed
	 *
	 * @param array $vars
	 */
	public function setValues($vars)
	{
		foreach ($vars as $name => $value)
		{
			// Look to see if the value is for a composite field
			if (isset($this->fields[$name]))
			{
				$this->fields[$name]->value = $value;
			}
			elseif (stripos($name, '__'))
			{
				$field = substr($name, 0, stripos($name, '__'));
				$composite = substr($name, stripos($name, '__') + 2);

				if (isset($this->fields[$field]))
				{
					$fld = $this->getField($field);

					if (is_a($fld, 'formslib_composite'))
					{
						$this->fields[$field]->composite_values[$composite] = $value;
					}
					elseif (is_a($fld, 'formslib\Field\MultiValue'))
					{
						$this->fields[$field]->multi_values[$composite] = $value;
					}
				}
			}
		}
	}

	/**
	 * Sets the HTML to output against mandatory fields
	 *
	 * @param string $html
	 *        	HTML code for mandatory fields
	 * @param string $semimandatoryhtml
	 *        	HTML code for fields where at least one of a set is requiredS
	 */
	public function setMandatoryHTML($html, $semimandatoryhtml = '')
	{
		$this->mandatoryHTML = $html;
		$this->semimandatoryHTML = $semimandatoryhtml;
	}

	public function setOptionalHTML($html)
	{
	    $this->optionalHTML = $html;
	}

	/**
	 * Output the form
	 */
	public function display()
	{
		$method = ($this->method == FORMSLIB_METHOD_GET) ? 'GET' : 'POST';
		echo CRLF . '<form name="' . $this->name . '" method="' . $method . '" action="' . $this->action . '"' . $this->_custom_attr() . $this->_class_attr() . '>' . CRLF . CRLF;

		echo $this->_header() . CRLF . CRLF;

		echo $this->htmltop . CRLF . CRLF;

		// Display any errors
		$this->_displayErrors();

		// Output any hidden fields
		$fields = array_keys($this->fields);
		foreach ($fields as $field)
		{
			if (is_a($this->fields[$field], 'formslib_hidden'))
			{
				echo $this->fields[$field]->getHTML() . CRLF;
			}
		}

		// Go through the fieldsets
		$field_js = [];
		foreach ($this->fsorder as $fieldset)
		{
			$fs =& $this->fieldsets[$fieldset];

			$submit = ($fieldset == $this->submitfieldset) ? true : false;

			$fs->display($this, $submit);

			// Get any JavaScript the fields need
			$field_js = array_merge($field_js, $fs->getJs($this));
		}

		echo $this->htmlbeforesubmit . CRLF . CRLF;

		if ($this->submitfieldset === false && $this->nosubmitbutton === false)
		{
			// Display a submit button
			echo $this->getSubmitHtml();
		}



		$this->_displayBottom($field_js);
	}

	public function validate()
	{
		// TODO: Decide whether to write this function
		return false;
	}

	public function validate_vars($vars)
	{
		$is_valid = true;

		$bootstrap3 = ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3 || $this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3_INLINE || $this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3_VERTICAL);

		// Loop through the fields and check mandatory fields are entered
		$fields = array_keys($this->fields);
		foreach ($fields as $name)
		{
		    $mandatory = $this->fields[$name]->mandatory;

		    /**
		     * @todo: Should mandatory checking be delegated...?
		     * 1. To individual fields
		     * 2. To a rule paradigm
		     */

			if (is_a($this->fields[$name], 'formslib_composite'))
			{
				if ($mandatory)
				{
					// TODO: Should validate on ticklist mean ALL ticked or at least one? Inherited behaviour is ALL ticked.

					$missing = false;
					foreach ($this->fields[$name]->get_composites() as $key)
					{
						if (! isset($vars[$name . '__' . $key]) || $vars[$name . '__' . $key] == '') $missing = true;

						if (is_a($this->fields[$name], 'formslib_date') && $vars[$name . '__' . $key] == 0) $missing = true;
					}

					if ($missing)
					{
						$this->fields[$name]->valid = false;
						$this->fields[$name]->addClass('formslibinvalid');

						if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP) $this->fields[$name]->addGroupClass('error');
						if ($bootstrap3) $this->fields[$name]->addGroupClass('has-error');

						// Add field to error list
						$label = $this->fields[$name]->label;
						$this->errorlist[] = [
							'name' => $name,
							'label' => $label,
							'message' => (! is_a($this->fields[$name], 'formslib_checkbox')) ? 'You must enter a value for ' . $label : 'You must tick "' . $label . '" to be able to complete this form'
						];
						$is_valid = false;
					}
				}
			}
			elseif (is_a($this->fields[$name], 'formslib\Field\MultiValue'))
			{
			    if ($mandatory)
			    {
			        $missing = false;

			        if(!isset($vars[ $name . '__0']) || ($vars[ $name . '__0']) == '')
			        {
			            $missing = true;
			        }

			        if ($missing)
			        {
			            $this->fields[$name]->valid = false;
			            $this->fields[$name]->addClass('formslibinvalid');

			            if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP) $this->fields[$name]->addGroupClass('error');
			            if ($bootstrap3) $this->fields[$name]->addGroupClass('has-error');

			            // Add field to error list
			            $label = $this->fields[$name]->label;
			            $this->errorlist[] = [
			                'name' => $name,
			                'label' => $label,
			                'message' => (! is_a($this->fields[$name], 'formslib_checkbox')) ? 'You must enter a value for ' . $label : 'You must tick "' . $label . '" to be able to complete this form'
			            ];
			            $is_valid = false;
			        }
			    }
			}
			elseif (! is_a($this->fields[$name], 'formslib_file'))
			{
				if ($mandatory && (! isset($vars[$name]) || $vars[$name] === ''))
				{
					$this->fields[$name]->valid = false;
					$this->fields[$name]->addClass('formslibinvalid');

					if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP) $this->fields[$name]->addGroupClass('error');
					if ($bootstrap3) $this->fields[$name]->addGroupClass('has-error');

					// Add field to error list
					$label = $this->fields[$name]->label;
					$this->errorlist[] = [
						'name' => $name,
						'label' => $label,
						'message' => (! is_a($this->fields[$name], 'formslib_checkbox')) ? 'You must enter a value for ' . $label : 'You must tick "' . $label . '" to be able to complete this form'
					];
					$is_valid = false;
				}
			}
			else
			{
				// TODO: Validate mandatory file fields
			}
		}

		// Loop through the fields and check the validation rules
		foreach ($fields as $name)
		{
			// Validate field
			if (is_a($this->fields[$name], 'formslib_composite'))
			{
				$cv = [];
				foreach ($this->fields[$name]->get_composites() as $key)
				{
					$cv[$key] = (isset($vars[$name . '__' . $key])) ? $vars[$name . '__' . $key] : null;
				}

				$valid = $this->fields[$name]->validate($cv);

				if (!$valid)
				{
					$this->fields[$name]->valid = false;
					$this->fields[$name]->addClass('formslibinvalid');

					if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP) $this->fields[$name]->addGroupClass('error');
					if ($bootstrap3) $this->fields[$name]->addGroupClass('has-error');

					$is_valid = false;

					$this->errorlist = array_merge($this->errorlist, $this->fields[$name]->getErrors());
				}
			}
			elseif (is_a($this->fields[$name], 'formslib_multiselect'))
			{
				// TODO: Validate multiselect fields
			}
			elseif (is_a($this->fields[$name], 'formslib\Field\MultiValue'))
			{
			    $count = 0;

			    $mv = [];
			    while (isset($vars[$name . '__'. $count]) && ($vars[$name . '__'. $count]) != '')
			    {
			        $count++;
			        $mv[] = $vars[$name . '__'. $count];
			    }

			    $valid = $this->fields[$name]->validate($mv);

			    if (!$valid)
			    {
			        $this->fields[$name]->valid = false;
			        $this->fields[$name]->addClass('formslibinvalid');

			        if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP) $this->fields[$name]->addGroupClass('error');
			        if ($bootstrap3) $this->fields[$name]->addGroupClass('has-error');

			        $is_valid = false;

			        $this->errorlist = array_merge($this->errorlist, $this->fields[$name]->getErrors());
			    }

 			}
			elseif (! is_a($this->fields[$name], 'formslib_file') && ! is_a($this->fields[$name], 'formslib_checkbox') && ! is_a($this->fields[$name], 'formslib_radio'))
			{
				$data = (isset($vars[$name])) ? $vars[$name] : null;

				$valid = $this->fields[$name]->validate($data);

				if (! $valid)
				{
					$this->fields[$name]->valid = false;
					$this->fields[$name]->addClass('formslibinvalid');

					if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP) $this->fields[$name]->addGroupClass('error');
					if ($bootstrap3) $this->fields[$name]->addGroupClass('has-error');

					$is_valid = false;

					$this->errorlist = array_merge($this->errorlist, $this->fields[$name]->getErrors());
				}
			}
		}

		return $is_valid;
	}

	public function getOutputStyle()
	{
		return $this->outputstyle;
	}

	public function getErrorList()
	{
		return $this->errorlist;
	}

	/**
	 * Add an error to the form
	 *
	 * @param string $name
	 *        	Field name attribute (HTML)
	 * @param string $label
	 *        	Field label
	 * @param string $message
	 *        	The message to display to the user
	 *
	 * @todo Call this function within validate_vars
	 */
	public function addError($name, $label, $message)
	{
		$this->errorlist[] = [
			'name' => $name,
			'label' => $label,
			'message' => $message
		];

		if (isset($this->fields[$name])) $this->fields[$name]->addClass('formslibinvalid');
	}

	public function getDataDump()
	{
		// Output any hidden fields
		$fields = array_keys($this->fields);

		$data = [];
		foreach ($fields as $field)
		{
			if (is_a($this->fields[$field], 'formslib_hidden'))
			{
				$data[$field] = $this->fields[$field]->getDataDump();
			}
		}

		// Go through the fieldsets
		foreach ($this->fsorder as $fieldset)
		{
			$data += $this->fieldsets[$fieldset]->getDataDump($this);
		}

		return $data;
	}

	public function getEmailBody($style)
	{
		$body = '';

		// Output any hidden fields
		$fields = array_keys($this->fields);
		$first = true;

		foreach ($fields as $field)
		{
			if (is_a($this->fields[$field], 'formslib_hidden'))
			{
				if (! $this->fields[$field]->getDoNotEmail())
				{
					switch ($style)
					{
						case FORMSLIB_EMAILSTYLE_HTML:
						case FORMSLIB_EMAILSTYLE_HTML_TH:
							if ($first)
							{
								$body .= '<table class="table">' . CRLF;
								$first = false;
							}
							$body .= '<tr>' . CRLF;
							$body .= ($style == FORMSLIB_EMAILSTYLE_HTML_TH) ? '<th>' . $field . '</th>' . CRLF : '<td>' . $field . '</td>' . CRLF;
							$body .= '<td>' . $this->fields[$field]->getEmailValue() . '</td>' . CRLF;
							$body .= '</tr>' . CRLF;
							break;

						default:
							$body .= $field . ':' . CRLF;
							$body .= $this->fields[$field]->getEmailValue() . CRLF . CRLF;
							break;
					}
				}
			}
		}

		if ($first === false && ($style == FORMSLIB_EMAILSTYLE_HTML || $style == FORMSLIB_EMAILSTYLE_HTML_TH))
		{
			$body .= '</table>' . CRLF;
		}

		// Go through the fieldsets
		foreach ($this->fsorder as $fieldset)
		{
			$body .= $this->fieldsets[$fieldset]->getEmailBody($this, $style);
		}

		// TODO: Display any fields not in fieldsets
		// TODO: Review if the above is acutually necessary, or whether it should fail instead

		return $body;
	}

	public function setHTMLTop($html)
	{
		$this->htmltop = $html;
	}

	public function setHTMLBottom($html)
	{
		$this->htmlbottom = $html;
	}

	public function setHTMLBeforeSubmit($html)
	{
		$this->htmlbeforesubmit = $html;
	}

	public function addClass($classname)
	{
		$this->classes[] = $classname;
	}

	public function addAttr($attr, $value)
	{
		$this->attrib[$attr] = $value;
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

	private function _custom_attr()
	{
		$attr_str = '';
		foreach ($this->attrib as $attrib => $value)
		{
			$attr_str .= ' ' . $attrib . '="' . $value . '"';
		}

		return $attr_str;
	}

	public function setSubmitFieldset($fieldset)
	{
		if (isset($this->fieldsets[$fieldset]))
		{
			$this->submitfieldset = $fieldset;
		}
		else
		{
			return false; //TODO: Throw exception?
		}
	}

	public function clearSubmitFieldset()
	{
		$this->submitfieldset = false;
	}

	public function setJqueryValidate($validate = true)
	{
		$this->jqueryvalidate = $validate;
	}

	public function setObfuscateJS($obfuscate = true)
	{
		$this->obfuscate_js = $obfuscate;
	}

	private function _generate_jquery_validation()
	{
	    $bootstrap3 = ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3 || $this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3_INLINE || $this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3_VERTICAL);

		// Generic stuff
		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP)
		{
			$jq = <<<EOF
$(document).ready(function(){
$('.formslib_jq_mand').focus(function(){
	$(this).removeClass('formslibinvalid');
	$(this).parent().parent().removeClass('error');
});

$('.formslib_jq_mand').blur(function(){
	if ($(this).val() == '')
	{
		$(this).addClass('formslibinvalid');
		$(this).parent().parent().addClass('error');
	}
});
EOF;
		}
		elseif ($bootstrap3)
		{
			$jq = <<<EOF
$(document).ready(function(){
$('.formslib_jq_mand').focus(function(){
	$(this).removeClass('formslibinvalid');
	$(this).parents('.form-group').removeClass('has-error');
});

$('.formslib_jq_mand').blur(function(){
	if ($(this).val() == '')
	{
		$(this).addClass('formslibinvalid');
		$(this).parents('.form-group').addClass('has-error');
	}
});
EOF;
		}
		else
		{
			$jq = <<<EOF
$(document).ready(function(){
$('.formslib_jq_mand').focus(function(){
	$(this).removeClass('formslibinvalid');
});

$('.formslib_jq_mand').blur(function(){
	if ($(this).val() == '')
	{
		$(this).addClass('formslibinvalid');
	}
});
EOF;
		}

		$fields = array_keys($this->fields);
		foreach ($fields as $name)
		{
			$conditions = $this->fields[$name]->get_jquery_conditions();

			if (count($conditions) > 0)
			{
				if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP)
				{
					$jq .= "
$('[name=$name]').focus(function(){
	$(this).removeClass('formslibinvalid');
	$(this).parent().parent().removeClass('error');
});";
				}
				elseif ($bootstrap3)
				{
					$jq .= <<<EOF
$('[name=$name]').focus(function(){
	$(this).removeClass('formslibinvalid');
	$(this).parents('.form-group').removeClass('has-error');
});
EOF;
				}
				else
				{
					$jq .= "
$('[name=$name]').focus(function(){
	$(this).removeClass('formslibinvalid');
});";
				}

				foreach ($conditions as $condition)
				{
					$jq .= "
$('[name=$name]').blur(function(){
	val = $(this).val();

	if (val == '') return;

	";

					$jq .= $condition;

					if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP)
					{
						$jq .= "
		$(this).parent().parent().addClass('error');
		$(this).addClass('formslibinvalid');
	}
});
";
					}
					elseif ($bootstrap3)
					{
						$jq .= <<<JS
		$(this).addClass('formslibinvalid');
		$(this).parents('.form-group').addClass('has-error');
	}
});
JS;
					}
					else
					{
						$jq .= "
		$(this).addClass('formslibinvalid');
	}
});
";
					}
				}
			}
		}

		$jq .= '});
';

		if ($this->obfuscate_js)
		{
			// Obfuscate returned JS
			$jqp = $jq;
			$jqp = str_replace("\\\r\n", "\\n", $jqp);
			$jqp = str_replace("\\\n", "\\n", $jqp);
			$jqp = str_replace("\\\r", "\\n", $jqp);
			$jqp = str_replace("}\r\n", "};\r\n", $jqp);
			$jqp = str_replace("}\n", "};\n", $jqp);
			$jqp = str_replace("}\r", "};\r", $jqp);

			$myPacker = new \JavaScriptPacker($jqp, 62, true, false);
			$packed = $myPacker->pack();
			unset($myPacker);

			return $packed;
		}
		else
		{
			return $jq;
		}
	}

	public function displayTopOnly()
	{
		// Output the form
		$method = ($this->method == FORMSLIB_METHOD_GET) ? 'GET' : 'POST';
		echo CRLF . '<form name="' . $this->name . '" method="' . $method . '" action="' . $this->action . '"' . $this->_custom_attr() . $this->_class_attr() . '>' . CRLF . CRLF;

		echo $this->htmltop . CRLF . CRLF;

		// Display any errors
		$this->_displayErrors();

		// Output any hidden fields
		$fields = array_keys($this->fields);
		foreach ($fields as $field)
		{
			if (is_a($this->fields[$field], 'formslib_hidden'))
			{
				echo $this->fields[$field]->getHTML() . CRLF;
			}
		}
	}

	private function _displayErrors()
	{
		if (count($this->errorlist) > 0)
		{
			if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP)
			{
				$classes = 'alert alert-block alert-error';
			}
			elseif ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3 || $this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3_INLINE || $this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3_VERTICAL)
			{
				$classes = 'alert alert-block alert-danger';
			}
			else
			{
				$classes = '';
			}

			echo '<ul class="' . $classes . '">' . CRLF;

			foreach ($this->errorlist as $err)
			{
				echo '<li class="error">';
				echo $err['message'];
				echo '</li>' . CRLF;
			}

			echo '</ul>' . CRLF;
		}
	}

	private function _displayBottom(&$field_js)
	{
		echo $this->htmlbottom . CRLF . CRLF;

		echo '</form>' . CRLF . CRLF;

		if ($this->jqueryvalidate)
		{
			echo '<script type="text/javascript">' . CRLF . '<!--' . CRLF;
			echo $this->_generate_jquery_showhide() . CRLF;
			echo $this->_generate_jquery_validation() . CRLF;
			echo $this->customjs . CRLF;
			foreach ($field_js as $fjs)
			{
				echo $fjs.CRLF; //TODO: Obfuscate?
			}

			if (!is_null($this->doubleClickTimeout))
			{
				$name = $this->name;
				$timeout = $this->doubleClickTimeout * 1000;
				$class = implode(' ', $this->submitclass);

				echo <<<EOF
$(document).ready(function(){
	$('form[name="$name"] input[type="submit"]').click(function(e){
		var btn = $(e.target);

		btn.parent().append('<a id="submitting" class="$class" disabled="disabled"><i class="fa fa-spinner fa-pulse"></i> Processing...</a>');
		btn.hide();

		window.setTimeout(function(){
			$(e.target).parent().children('a#submitting').hide();
			$(e.target).show();
		}, $timeout);
	});
});
EOF;
			}

			echo '//-->' . CRLF . '</script>' . CRLF . CRLF;
		}
	}

	public function displayBottomOnly()
	{
		$js = [];

		$this->_displayBottom($js);
	}

	public function displayRawLabel($fieldname)
	{
		if (! isset($this->fields[$fieldname]))
		{
			echo '<p>FORMSLIB ERROR: undefined field: ' . Security::escapeHtml($fieldname) . '</p>';
			return '';
		}
		return '<label for="' . $fieldname . '">' . Security::escapeHtml($this->fields[$fieldname]->label) . '</label>';
	}

	public function displayRawField($fieldname)
	{
		if (! isset($this->fields[$fieldname]))
		{
			echo '<p>FORMSLIB ERROR: undefined field: ' . Security::escapeHtml($fieldname) . '</p>';
			return '';
		}

		$mand = ($this->fields[$fieldname]->mandatory) ? $this->mandatoryHTML : '';
		return $this->fields[$fieldname]->getHTML() . $mand;
	}

	public function displayFormattedField($fieldname)
	{
		if (! isset($this->fields[$fieldname]))
		{
			echo '<p>FORMSLIB ERROR: undefined field: ' . Security::escapeHtml($fieldname) . '</p>';
			return '';
		}

		return $this->fields[$fieldname]->display($this);
	}

	public function setNoSubmitButton($setting = true)
	{
		$this->nosubmitbutton = $setting;
	}

	public function removeField($fieldname)
	{
	    if (! is_array($fieldname)) $fieldname = array(
			$fieldname
		);

		foreach ($fieldname as $fn)
		{
			unset($this->fields[$fn]);
		}
	}

	public function appendHTMLtop($html)
	{
		$this->htmltop .= $html;
	}

	public function appendHTMLbottom($html)
	{
		$this->htmltop .= $html;
	}

	public function setCustomJS($js)
	{
		$this->customjs = $js;
	}

	private function _header()
	{
		$set_headers = $headers = [];

		foreach ($this->types_used as $type)
		{
			$class_set = '';

			if (substr($type, 0, 1) == '\\')
			{
				$class = $type;
			}
			else
			{
				$class = class_exists('formslib\Field\\'.$type) ? 'formslib\Field\\'.$type : 'formslib_'.$type;
			}

			$hdr = $class::getHeader($class_set);

			if (! in_array($class_set, $set_headers))
			{
				$set_headers[] = $class_set;

				if ($hdr != '') $headers[] = $hdr;
			}
		}

		return implode("\r\n", $headers);
	}

	public function displayFormattedFieldReadOnly($fieldname)
	{
		if (! isset($this->fields[$fieldname]))
		{
			echo '<p>FORMSLIB ERROR: undefined field: ' . Security::escapeHtml($fieldname) . '</p>';
			return '';
		}

		return $this->fields[$fieldname]->displayReadOnly($this);
	}

	public function displayRawFieldReadOnly($fieldname)
	{
		if (! isset($this->fields[$fieldname]))
		{
			echo '<p>FORMSLIB ERROR: undefined field: ' . Security::escapeHtml($fieldname) . '</p>';
			return '';
		}

		$mand = ($this->fields[$fieldname]->mandatory) ? $this->mandatoryHTML : '';
		return $this->fields[$fieldname]->getHTMLReadOnly() . $mand;
	}

	/**
	 * Get a field by name
	 * @param string $fieldname
	 * @return \formslib_field_paramset
	 */
	public function &getField($fieldname)
	{
	    if (!isset($this->fields[$fieldname]))
	        throw new \Exception('Unable to retrieve undefined field "'.$fieldname.'"');

	    return $this->fields[$fieldname];
	}

	/**
	 * Get a fieldset by name
	 * @param string $fsname
	 * @return Fieldset
	 */
	public function &getFieldSet($fsname)
	{
	    if (!isset($this->fieldsets[$fsname]))
	        throw new \Exception('Unable to retrieve undefined fieldset "'.$fsname.'"');

	    return $this->fieldsets[$fsname];
	}

	public function addSubmitClass($classname)
	{
		$this->submitclass[] = $classname;
	}

	public function getSubmitHtml()
	{
		$pre = '';
		$post = '';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3)
		{
			if ($this->submit_grid_ratio > 0)
			{
				$offset = $this->submit_grid_ratio;
				$cols = 12-$offset;

				$pre = '<div class="form-group"><div class="col-sm-'.$cols.' col-sm-offset-'.$offset.'">';
				$post = '</div></div>';
			}
		}
		elseif ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3_INLINE)
		{
			// Append nothing
		}
		else
		{
			// TODO: Review the proper use of HTML here
			$pre = '<p>';
			$post = '</p>';
		}

		return $pre.'<input type="submit" name="submit" value="' . Security::escapeHtml($this->submitlabel) . '" class="'.implode(' ', $this->submitclass).'" />'.$post . CRLF;
	}

	public function &setSubmitGridRatio($label_cols)
	{
		$this->submit_grid_ratio = $label_cols;

		return $this;
	}

	/**
	 * Sets the PHP class to create result objects from
	 *
	 * @var string $class Fully qualified class name
	 */
	public function setResultClass($class)
	{
		$this->resultClass = $class;
	}

	/**
	 * @return \formslib\Result\ResultObject
	 */
	public function getResultObject()
	{
		$result = new $this->resultClass();

		$fields = array_keys($this->fields);
		foreach ($fields as $field)
		{
			if (is_a($this->fields[$field], 'formslib_hidden'))
			{
				if (! $this->fields[$field]->getNoObject())
				{
					$result->{$field} = $this->fields[$field]->getObjectValue();
				}
			}
		}

		// Go through the fieldsets
		foreach ($this->fsorder as $fieldset)
		{
			$this->fieldsets[$fieldset]->buildResultObject($this, $result);
		}

		return $result;
	}

	/**
	 * Does a named fieldset exist?
	 * @param string $fsname
	 * @return boolean
	 */
	public function doesFieldsetExist($fsname)
	{
		return (isset($this->fieldsets[$fsname])) ? true : false;
	}

	public function &disableAllFields()
	{
		$fields = array_keys($this->fields);
		foreach ($fields as $field)
		{
			if (!is_a($this->fields[$field], 'formslib_hidden'))
			{
				$this->fields[$field]->setDisabled();
			}
		}

		return $this;
	}

	public function &setDoubleClickProtection($timeout = 30)
	{
		$this->doubleClickTimeout = $timeout;

		return $this;
	}

	public function positionFieldsetTo($fsname, $location)
	{
		if ($location < 1 || $location > count($this->fsorder)) throw new \Exception('Invalid fieldset position');

		$oldpos = (array_search($fsname, $this->fsorder));

		if ($oldpos === false) throw new \Exception('Field not found');

		unset($this->fsorder[$oldpos]);

		array_splice($this->fsorder, $location-1, 0, array($fsname));
	}

	public function positionFieldsetBelow($fsname, $below)
	{
		$orig = array_search($fsname, $this->fsorder);
		if ($orig === false) throw new \Exception('Fieldset not found');

		$pos = array_search($below, $this->fsorder);
		if ($pos === false) throw new \Exception('Reference fieldset not found');

		$location = ($pos >= $orig) ? $pos + 1 : $pos + 2;

		$this->positionFieldsetTo($fsname, $location);
	}

	public function positionFieldsetAbove($fsname, $above)
	{
		$orig = array_search($fsname, $this->fsorder);
		if ($orig === false) throw new \Exception('Fieldset not found');

		$pos = array_search($above, $this->fsorder);
		if ($pos === false) throw new \Exception('Reference fieldset not found');

		$location = ($pos > $orig) ? $pos : $pos + 1;

		$this->positionFieldsetTo($fsname, $location);
	}

	public function setOptionalLabels($optional = true)
	{
	    $this->optionalLabels = true;
	}

	public function getOptionalLabels()
	{
	    return $this->optionalLabels;
	}

	private function _generate_jquery_showhide()
	{
		$conditions = [];

		foreach ($this->fieldsets as $fs)
		{
			$condition = $fs->getDisplayCondition();

			if (is_object($condition))
			{
				$conditions[$condition->getFieldName()][] = ['fs', $fs->getName(), $condition];
			}
		}

		foreach ($this->fields as $fld)
		{
			$condition = $fld->getDisplayCondition();

			if (is_object($condition))
			{
				$conditions[$condition->getFieldName()][] = ['fld', $fld->getName(), $condition];
			}
		}

		if (!count($conditions)) return null;

		$jq = '';

		foreach ($conditions as $name => $c)
		{
			$jq .= <<<JS

$('[name=$name]').change(function(e){
JS;

			$type = $c[0];
			$id = $c[1];
			$val = $c[2]->getValue();

			switch ($c[2]->getOperator())
			{
				case \formslib\Operator::EQ:
					$jq .= <<<JS
if ($(e.target).val() == '$val')
{
	$('#{$type}_$id').show();
}
else
{
	$('#{$type}_$id').hide();
}
JS;
					break;

				case \formslib\Operator::IN:
					$jq .= <<<JS
// TODO: Conditional display on IN
JS;
					break;

				default:
					throw new \Exception('Unable to process display condition operator "'.$c[3]->getOperator().'"');
					break;
			}

			$jq .= '});';
		}

		if ($this->obfuscate_js)
		{
			// Obfuscate returned JS
			$jqp = $jq;
			$jqp = str_replace("\\\r\n", "\\n", $jqp);
			$jqp = str_replace("\\\n", "\\n", $jqp);
			$jqp = str_replace("\\\r", "\\n", $jqp);
			$jqp = str_replace("}\r\n", "};\r\n", $jqp);
			$jqp = str_replace("}\n", "};\n", $jqp);
			$jqp = str_replace("}\r", "};\r", $jqp);

			$myPacker = new \JavaScriptPacker($jqp);
			$packed = $myPacker->pack();
			unset($myPacker);

			return $packed;
		}
		else
		{
			return $jq;
		}
	}
}