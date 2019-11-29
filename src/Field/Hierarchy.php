<?php
namespace formslib\Field;

class Hierarchy extends MultiValue
{
	protected $tree;
	protected $maxlevels;
	protected $clientside;

	public function &setOptions($tree)
	{
		$this->tree = $tree;

		return $this;
	}

	public function &setMaxLevels($levels)
	{
		$this->maxlevels = $levels;

		return $this;
	}

	public function getHTML()
	{
		$class_str = 'formslib-multivalue-container';

		if ($this->outputstyle == FORMSLIB_STYLE_BOOTSTRAP3) $class_str .= ' row';

		$output = '<div class="'.$class_str.'">';

		$output .= $this->_recurseGetHTML($this->tree, 0);

		$output .= '</div><!-- /.formslib-multivalue-container -->';

		return $output;
	}

	private function _recurseGetHTML($tree, $level)
	{
		if (isset($this->multi_values[$level]))
		{
			$value = $this->multi_values[$level];

			$output = $this->_buildDropdown($tree['children'], $level, $value);

			// TODO: Find the children tree properly

// 			echo '<pre>';
// 			print_r($tree['children'][$value]);
// 			echo '</pre>';

			if (isset($tree['children'][$value]) && ($this->maxlevels < 1 || $level + 1 < $this->maxlevels) && count($tree['children'][$value]['children']))
			{
				$output .= $this->_recurseGetHTML($tree['children'][$value], $level+1);
			}
		}
		else
		{
			$output = $this->_buildDropdown($tree['children'], $level, null);
		}

		return $output;
	}

	private function _buildDropdown($tree, $level, $selected)
	{
		$options = ['' => '- Please select -'];

		foreach ($tree as $dat)
		{
			$options[$dat['value']] = $dat['label'];
		}

		$field = new \formslib_select($this->name.'__'.$level);
		$field->setOptions($options)->addClass('form-control')->addAttr('data-formslib-field', $this->name);
		$field->value = $selected;

		$output = '';

		// TODO: Labelling
		// echo '	<label class="control-label col-sm-' . $col_label . '" for="fld_' . Security::escapeHtml($this->name) . '">' . Security::escapeHtml($this->label) . $mand . '</label> ' . CRLF;

		$output .=  '	<div class="col-xs-12">' . CRLF;
		$output .= $field->getHTML() . CRLF;
		$output .=  '	</div><!--/.col-xs-12-->' . CRLF;

		return $output;
	}

	public function getJs()
	{
		$js = parent::getJs();

		$path = FORMSLIB_AJAX_SERVICE;

		$js[] = <<<EOF
$(document).on('change', 'select[data-formslib-field="{$this->name}"]', function(e){
	var caller = this;
	field = $(this).attr('data-formslib-field');
	subfield = $(this).attr('name');
	value = $(this).val();

	// Get ALL the values
	var values = [];
	$('select[data-formslib-field="{$this->name}"]').each(function(index){
		values.push({ name: $(this).attr('name'), val: $(this).val() })
	});

	param = {
		formslib_action: 'getNextLevel',
		formslib_form_identifier: '{$this->ajaxFormIdentifier}',
		field: field,
		subfield: subfield,
		value: value,
		vals: JSON.stringify(values)
	};

	//TODO: If data held locally, deal with it here

	$.ajax({
		url: '$path',
		data: param,
		method: 'POST',
		dataType: 'json',
		error: function (XMLHttpRequest, textStatus, errorThrown)
		{
			if (textStatus == 'parsererror')
			{
				alert('Returned: '+XMLHttpRequest.responseText);
			}
			else
			{
				alert('Communication error: '+textStatus+' '+XMLHttpRequest.status+' - '+errorThrown);
			}
		},
		success: function(rtndata)
		{
			$(caller).parent().nextAll().remove();
			$(caller).parent().after(rtndata.html);

			// TODO: Or visually indicate furthest branch reached if applicable
		}
	});
});
EOF;

		return $js;
	}

	public function getNextLevelAjax($subfield, $values)
	{
		$matches = [];
		preg_match('/^(.*)__([0-9]+)$/', $subfield, $matches);

		//TODO: Check for lack of matches

		$fld = $matches[1];
		$level = $matches[2];

		if ($this->maxlevels > 0 && $level+1 >= $this->maxlevels) return '';

		if (!function_exists('json_decode'))
		{
			$json = new \Services_JSON();
			$v = $json->decode($values);
		}
		else
		{
			$v = json_decode($values);
		}

		$vals = [];
		foreach ($v as $valobj)
		{
			$vals[$valobj->name] = $valobj->val;
		}

		// Process the tree

		$children = $this->tree['children'];
		for ($i = 0; $i <= $level; $i++)
		{
			$found = false;
			foreach ($children as $index => $child)
			{
				if ($child['value'] == $vals[$fld.'__'.$i])
				{
					$found = true;
					$children = $children[$index]['children'];
				}
			}

			if (!$found)
			{
				$children = []; // TODO: Return a message instead?
			}
		}

		if (!count($children)) return '<!-- No more levels --><p>No more levels</p>';

		return $this->_buildDropdown($children, $level+1, null);
	}

	public function getEmailValue()
	{
		return implode(' > ', $this->multi_values);
	}
}