<?php
namespace formslib\BootstrapUtil;

/**
 * Utility class to create HTML code for a Bootstrap alerts
 */
class Alert
{
	const ALERT_SUCCESS = 'success';
	const ALERT_INFO = 'info';
	const ALERT_WARNING = 'warning';
	const ALERT_DANGER = 'danger';
	const ALERT_ERROR = 'danger';

	private $context;
	private $icon;
	private $content;

	public function __construct()
	{

	}

	public static function &gen()
	{
		$alert = new self();
		return $alert;
	}

	public function &setContext($context)
	{
		//TODO: Validate

		$this->context = $context;

		return $this;
	}

	public function &setText($text)
	{
		$this->content = htmlentities($text);

		return $this;
	}

	public function &setHtml($html)
	{
		$this->content = $html;

		return $this;
	}

	public function setIcon($icon)
	{
		//TODO: Validate
		$this->icon = $icon;
	}

	public function getHtml()
	{
		$html = '<p class="alert alert-'.$this->context.'">';

		if ($this->icon != '')
		{
			$html .= '<i class="fa fa-fw fa-'.$this->icon.'"></i> '; //TODO: Accessibility tagging
		}

		$html .= $this->content;
		$html .= '</p>';

		return $html;
	}

	public static function rsuccess($alert, $html = false)
	{
		$a = new self();
		$a->setContext('success')->setIcon('check');

		if ($html) $a->setHtml($alert);
		else $a->setText($alert);

		return $a->getHtml();
	}

	public static function rinfo($alert, $html = false)
	{
		$a = new self();
		$a->setContext('info')->setIcon('info');

		if ($html) $a->setHtml($alert);
		else $a->setText($alert);

		return $a->getHtml();
	}

	public static function rwarning($alert, $html = false)
	{
		$a = new self();
		$a->setContext('warning')->setIcon('exclamation-triangle');

		if ($html) $a->setHtml($alert);
		else $a->setText($alert);

		return $a->getHtml();
	}

	public static function rdanger($alert, $html = false)
	{
		$a = new self();
		$a->setContext('danger')->setIcon('exclamation');

		if ($html) $a->setHtml($alert);
		else $a->setText($alert);

		return $a->getHtml();
	}

	public static function rerror($alert, $html = false)
	{
		return self::rdanger($alert, $html);
	}

	public static function success($alert, $html = false)
	{
		echo self::rsuccess($alert, $html);
	}

	public static function info($alert, $html = false)
	{
		echo self::rinfo($alert, $html);
	}

	public static function warning($alert, $html = false)
	{
		echo self::rwarning($alert, $html);
	}

	public static function danger($alert, $html = false)
	{
		echo self::rdanger($alert, $html);
	}

	public static function error($alert, $html = false)
	{
		self::danger($alert, $html);
	}
}