<?php
final class Formslib
{
	/**
	 * Replace text line breaks with HTML tags
	 * @param string $text Input unformatted text
	 * @return string
	 */
	public static function convertTextToHtml($text)
	{
	    $htmltext = \formslib\Utility\Security::escapeHtml($text);
		$htmltext = str_replace("\r", '', $htmltext);
		$htmltext = str_replace("\n", "<br />\n", $htmltext);
		return $htmltext;
	}

	/**
	 * Handle 2- and 4-digit UK dates returning a DateTime object
	 *
	 * @param string $date_string
	 * @throws \Exception
	 * @return \DateTime
	 */
	public static function &getUkDate($date_string)
	{
		$string = trim($date_string);

		if (!preg_match('|^[0-9]{1,2}/[0-9]{1,2}/[0-9]{2,4}$|', $string)) throw new \Exception('Invalid date format');

		$parts = explode('/', $string);

		if (strlen($parts[2]) == 2)
		{
			$dt = \DateTime::createFromFormat('!d/m/y', $string);
		}
		else
		{
			$dt = \DateTime::createFromFormat('!d/m/Y', $string);
		}

		return $dt;
	}
}
