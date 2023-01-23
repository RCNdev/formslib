<?php
namespace formslib\BootstrapUtil;

use formslib\Utility\Security;

/**
 * Utility class to create HTML code for a Bootstrap JS modal window
 */
class Modal
{
	protected $id;
	protected $headingLevel = 4;
	protected $title;
	protected $body;
	protected $footer;
	protected $js;
	protected $hasCloseButton = false;
	protected $dialogclass = '';

	/**
	 * Set the HTML id attribute
	 * @param string $id HTML id attibute for the overall modal
	 * @return \formslib\BootstrapUtil\Modal
	 */
	public function &setId($id)
	{
		$this->id = $id;

		return $this;
	}

	/**
	 * Set the heading level (defaults to h4)
	 * @param int $headingLevel The numeric heading level
	 * @throws \Exception
	 * @return \formslib\BootstrapUtil\Modal
	 */
	public function &setHeadingLevel($headingLevel)
	{
		if (!preg_match('/^[1-6]$/', $headingLevel)) throw new \Exception('Invalid heading level');

		$this->headingLevel = $headingLevel;

		return $this;
	}

	/**
	 * Set the title text, will be escaped
	 * @param string $title Title text
	 * @return \formslib\BootstrapUtil\Modal
	 */
	public function &setTitle($title)
	{
		$this->title = $this->_escape($title);

		return $this;
	}

	/**
	 * Set the HTML body
	 * @param string $body HTML body
	 * @return \formslib\BootstrapUtil\Modal
	 */
	public function &setBody($body)
	{
		$this->body = $body;

		return $this;
	}

	/**
	 * Set the HTML footer
	 * @param string $footer HTML footer
	 * @return \formslib\BootstrapUtil\Modal
	 */
	public function &setFooter($footer)
	{
		$this->footer = $footer;

		return $this;
	}

	/**
	 * Set any JavaScript code needed for the modal
	 * @param string $js Javascript code
	 * @return \formslib\BootstrapUtil\Modal
	 */
	public function &setJs($js)
	{
		$this->js = $js;

		return $this;
	}

	/**
	 * Set JavaScript to fire when the modal is shown
	 * @param string $js
	 */
	public function &setJsOnShow($js)
	{
		$lines = explode("\r\n", $js);

		$newline = [];
		foreach ($lines as $line)
		{
			$newline[] = "\t\t".$line;
		}

		$js = implode("\r\n", $newline);

		$this->js = <<<JS
$(document).ready(function(){
	$('#{$this->id}').on('show.bs.modal', function(event) {
		var button = $(event.relatedTarget);

$js
	});
});
JS;
		return $this;
	}

	/**
	 * Set whether the modal has a close button
	 * @param boolean $closeButton Defaults to true if called
	 * @return \formslib\BootstrapUtil\Modal
	 */
	public function &hasCloseButton($closeButton = true)
	{
		$this->hasCloseButton = $closeButton;

		return $this;
	}

	public function &setSize($size)
	{
		if ($size == 'lg') $this->dialogclass = ' modal-lg';
		elseif ($size == 'sm') $this->dialogclass = ' modal-sm';
		else $this->dialogclass = '';

		return $this;
	}

	/**
	 * Generates the HTML for a close button if needed
	 * @return string
	 */
	protected function _generateCloseButton()
	{
		return ($this->hasCloseButton) ? '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' : '';
	}

	/**
	 * Escape HTML
	 * @param string $input
	 * @return string
	 */
	protected function _escape($input)
	{
		return Security::escapeHtml($input);
	}

	/**
	 * Get the HTML/JS output
	 * @return string
	 */
	public function getOutput()
	{
		$js = '';

		if ($this->js != '')
		{
			$js = <<<HTML
<script type="text/javascript">
<!--
{$this->js}
//-->
</script>
HTML;
		}

		return <<<HTML


<div class="modal fade" id="{$this->id}" tabindex="-1" role="dialog" aria-labelledby="{$this->id}Label">
  <div class="modal-dialog{$this->dialogclass}" role="document">
    <div class="modal-content">
      <div class="modal-header">
{$this->_generateCloseButton()}
        <h{$this->headingLevel} class="modal-title" id="{$this->id}Label">{$this->title}</h{$this->headingLevel}>
      </div>
      <div class="modal-body">
{$this->body}
      </div>
      <div class="modal-footer">
{$this->footer}
      </div>
    </div>
  </div>
</div>
$js

HTML;
	}

	/**
	 * Echos the output
	 */
	public function display()
	{
		echo $this->getOutput();
	}
}
