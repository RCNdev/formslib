<?php
namespace formslib\Field;

abstract class GenericMulti extends MultiValue
{
	protected function _getAddButton()
	{
		return '<div class="col-xs-12 formslib-multi-add"><a class="btn btn-sm btn-success" href="#" id="add_'.$this->name.'"><i class="fa fa-plus"></i> Add</a></a>';
	}

	public function getHTML()
	{
		$this->_preProcessValues();

		$html = '<div class="row">';

		foreach ($this->indices as $i)
		{
			$html .= '	<div class="col-xs-12">' . CRLF;
			$html .= '	<div class="row"><div class="col-xs-11">' . CRLF;
			$html .= $this->getSingleInstance($i);
			$html .= '    </div><div class="col-xs-1"><a class="btn btm-sm btn-danger" title="Remove"><i class="fa fa-times"></i></a></div>';
			$html .= '  </div><!--/.row-->'.CRLF;
			$html .= '	</div><!--/.col-xs-12-->' . CRLF;
		}

		$html .= $this->_getAddButton();

		$html .= '</div><!-- /.row -->';

		return $html;
	}

	abstract protected function getSingleInstance($i);

	public function getJs()
	{
		$js = parent::getJs();

		$path = FORMSLIB_AJAX_SERVICE;

		//TODO: Generate addition javascript

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
}