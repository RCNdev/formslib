<?php
namespace formslib\Utility;

final class Security
{
    /**
     * Escape HTML using UTF-8 encoding (backwards compatibility for earlier PHP versions)
     * @param string $text Text to escape HTML control characters from
     */
    public static function escapeHtml($text)
    {
        $htmltext = htmlentities((is_null($text)) ? '' : $text, ENT_COMPAT, 'UTF-8');

        return $htmltext;
    }
}