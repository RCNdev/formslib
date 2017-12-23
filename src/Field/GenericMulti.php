<?php
namespace formslib\Field;

abstract class GenericMulti extends MultiValue
{
	protected $indices = array();

	abstract protected function _preProcessValues();

	protected function _getAddButton()
	{
		return '<div class="col-xs-12 formslib-multiadd"><a class="btn btn-sm btn-success" href="#" data-formslib-field="'.$this->name.'"><i class="fa fa-plus"></i> Add</a></div>';
	}

	public function getHTML()
	{
		$this->_preProcessValues();

		$html = '<input type="hidden" name="'.$this->name.'__control" value="'.implode(',', $this->indices).'" />'.CRLF;
		$html .= '<div class="row">';

		foreach ($this->indices as $i)
		{
			$html .= '	<div class="col-xs-12 formslib-multi-item">' . CRLF;
			$html .= '	<div class="row"><div class="col-xs-11">' . CRLF;
			$html .= $this->getSingleInstance($i, true);
			$html .= '    </div><div class="col-xs-1"><a class="btn btm-sm btn-danger formslib-multiremove" data-formslib-field="'.$this->name.'" title="Remove" data-index="'.$i.'"><i class="fa fa-times"></i></a></div>';
			$html .= '  </div><!--/.row-->'.CRLF;
			$html .= '	</div><!--/.col-xs-12-->' . CRLF;
		}

		$html .= $this->_getAddButton();

		$html .= CRLF.'</div><!-- /.row -->'.CRLF.CRLF;

		return $html;
	}

	abstract protected function getSingleInstance($i);

	public function getJs()
	{
		$js = parent::getJs();

		//$path = FORMSLIB_AJAX_SERVICE;

		$html = '';
		$html .= '	<div class="col-xs-12 formslib-multi-item">' . CRLF;
		$html .= '	<div class="row"><div class="col-xs-11">' . CRLF;
		$html .= $this->getSingleInstance('!!new!!');
		$html .= '    </div><div class="col-xs-1"><a class="btn btm-sm btn-danger formslib-multiremove" data-formslib-field="'.$this->name.'" title="Remove" data-index="!!new!!"><i class="fa fa-times"></i></a></div>';
		$html .= '  </div><!--/.row-->'.CRLF;
		$html .= '	</div><!--/.col-xs-12-->' . CRLF;

		$new = json_encode($html);

		$js[] = <<<EOF
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

$(document).on('click', '.formslib-multiadd a.btn[data-formslib-field="{$this->name}"]', function(e){
	field = $(this).data('formslib-field');

	var indices_str = $('input[name="'+field+'__control"').val();

	indices = indices_str.split(',');
	var largest = Math.max.apply(Math, indices);
	var next = largest+1;

	indices.push(next);

	indices_str = indices.join(',');
	$('input[name="'+field+'__control"').val(indices_str);

	var newblock = $new;

	newblock = newblock.replace(/!!new!!/g, next);

	$(this).parent().before(newblock);

	return false;
});
EOF;

		return $js;
	}
}