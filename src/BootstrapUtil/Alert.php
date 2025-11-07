<?php
namespace formslib\BootstrapUtil;

use formslib\Utility\Security;

/**
 * Utility class to create HTML code for a Bootstrap alerts
 */
class Alert
{
	public const ALERT_SUCCESS = 'success';
	public const ALERT_INFO = 'info';
	public const ALERT_WARNING = 'warning';
	public const ALERT_DANGER = 'danger';
	public const ALERT_ERROR = 'danger';
	public const ALERT_PRIMARY = 'primary';
	public const ALERT_SECONDARY = 'secondary';
	public const ALERT_LIGHT = 'light';
	public const ALERT_DARK = 'dark';

	public const ALERT_CONTEXTS = [
		self::ALERT_SUCCESS,
		self::ALERT_INFO,
		self::ALERT_WARNING,
		self::ALERT_DANGER,
		self::ALERT_PRIMARY,
		self::ALERT_SECONDARY,
		self::ALERT_LIGHT,
		self::ALERT_DARK,
	];

	private $context;
	private $icon;
	private $content;
	private $sronly = null;

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
		if (!in_array($context, self::ALERT_CONTEXTS))
		{
			throw new \UnexpectedValueException('Invalid context class');
		}

		$this->context = $context;

		return $this;
	}

	public function &setText($text)
	{
		$this->content = Security::escapeHtml($text);

		return $this;
	}

	public function &setHtml($html)
	{
		$this->content = $html;

		return $this;
	}

	public function &setIcon($icon)
	{
		//TODO: Validate
		$this->icon = $icon;

		return $this;
	}

	public function &setIconScreenReader($label)
	{
		$this->sronly = $label;

		return $this;
	}

	public function getHtml()
	{
		$html = '<p class="alert alert-'.$this->context.' bg-gradient" role="alert">';

		if ($this->icon != '')
		{
			$html .= '<i class="fa fa-fw fa-'.$this->icon.'" aria-hidden="true"></i>';

			if (!is_null($this->sronly))
			{
				$html .= '<span class="sr-only fa-sr-only visually-hidden">'.$this->sronly.'</span>';
			}

			$html .= ' ';
		}

		$html .= $this->content;
		$html .= '</p>'.PHP_EOL;

		return $html;
	}

	/**
	 * Return a success alert
	 *
	 * @param string $alert Alert contents (text/HTML)
	 * @param boolean $html
	 * @return string
	 */
	public static function rsuccess($alert, $html = false)
	{
		$a = new self();
		$a->setContext('success')->setIcon('check')->setIconScreenReader('Success:');

		if ($html)
		{
			$a->setHtml($alert);
		}
		else
		{
			$a->setText($alert);
		}

		return $a->getHtml();
	}

	/**
	 * Return an info alert
	 *
	 * @param string $alert Alert contents (text/HTML)
	 * @param boolean $html
	 * @return string
	 */
	public static function rinfo($alert, $html = false)
	{
		$a = new self();
		$a->setContext('info')->setIcon('info')->setIconScreenReader('Information:');

		if ($html)
		{
			$a->setHtml($alert);
		}
		else
		{
			$a->setText($alert);
		}

		return $a->getHtml();
	}

	/**
	 * Return a warning alert
	 *
	 * @param string $alert Alert contents (text/HTML)
	 * @param boolean $html
	 * @return string
	 */
	public static function rwarning($alert, $html = false)
	{
		$a = new self();
		$a->setContext('warning')->setIcon('exclamation-triangle')->setIconScreenReader('Warning:');

		if ($html)
		{
			$a->setHtml($alert);
		}
		else
		{
			$a->setText($alert);
		}

		return $a->getHtml();
	}

	/**
	 * Return a danger alert
	 *
	 * @param string $alert Alert contents (text/HTML)
	 * @param boolean $html
	 * @return string
	 */
	public static function rdanger($alert, $html = false)
	{
		$a = new self();
		$a->setContext('danger')->setIcon('exclamation')->setIconScreenReader('Error or danger:');

		if ($html)
		{
			$a->setHtml($alert);
		}
		else
		{
			$a->setText($alert);
		}

		return $a->getHtml();
	}

	/**
	 * Return a danger (error) alert
	 *
	 * @param string $alert Alert contents (text/HTML)
	 * @param boolean $html
	 * @return string
	 */
	public static function rerror($alert, $html = false)
	{
		return self::rdanger($alert, $html);
	}

	/**
	 * Output a success alert
	 *
	 * @param string $alert Alert contents (text/HTML)
	 * @param boolean $html
	 * @return void
	 */
	public static function success($alert, $html = false)
	{
		echo self::rsuccess($alert, $html);
	}

	/**
	 * Output an info alert
	 *
	 * @param string $alert Alert contents (text/HTML)
	 * @param boolean $html
	 * @return void
	 */
	public static function info($alert, $html = false)
	{
		echo self::rinfo($alert, $html);
	}

	/**
	 * Output a warning alert
	 *
	 * @param string $alert Alert contents (text/HTML)
	 * @param boolean $html
	 * @return void
	 */
	public static function warning($alert, $html = false)
	{
		echo self::rwarning($alert, $html);
	}

	/**
	 * Output a danger alert
	 *
	 * @param string $alert Alert contents (text/HTML)
	 * @param boolean $html
	 * @return void
	 */
	public static function danger($alert, $html = false)
	{
		echo self::rdanger($alert, $html);
	}

	/**
	 * Output a danger (error) alert
	 *
	 * @param string $alert Alert contents (text/HTML)
	 * @param boolean $html
	 * @return void
	 */
	public static function error($alert, $html = false)
	{
		self::danger($alert, $html);
	}
}