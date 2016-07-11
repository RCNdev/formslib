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
		$htmltext = htmlspecialchars($text);
		$htmltext = str_replace("\r", '', $htmltext);
		$htmltext = str_replace("\n", "<br />\n", $htmltext);
		return $htmltext;
	}
}
