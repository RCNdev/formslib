<?php
if (!defined('CRLF')) define('CRLF', "\r\n");

if (!defined('FORMSLIB_METHOD_GET')) define('FORMSLIB_METHOD_GET', 1);
if (!defined('FORMSLIB_METHOD_POST')) define('FORMSLIB_METHOD_POST', 2);

if (!defined('FORMSLIB_STYLE_P')) define('FORMSLIB_STYLE_P', 1);
if (!defined('FORMSLIB_STYLE_DL')) define('FORMSLIB_STYLE_DL', 2);
if (!defined('FORMSLIB_STYLE_BOOTSTRAP')) define('FORMSLIB_STYLE_BOOTSTRAP', 3);
if (!defined('FORMSLIB_STYLE_BOOTSTRAP3')) define('FORMSLIB_STYLE_BOOTSTRAP3', 4);
if (!defined('FORMSLIB_STYLE_BOOTSTRAP3_INLINE')) define('FORMSLIB_STYLE_BOOTSTRAP3_INLINE', 5);
if (!defined('FORMSLIB_STYLE_BOOTSTRAP3_VERTICAL')) define('FORMSLIB_STYLE_BOOTSTRAP3_VERTICAL', 6);

if (!defined('FORMSLIB_EMAILSTYLE_TEXT')) define('FORMSLIB_EMAILSTYLE_TEXT', 1);
if (!defined('FORMSLIB_EMAILSTYLE_HTML')) define('FORMSLIB_EMAILSTYLE_HTML', 2);
if (!defined('FORMSLIB_EMAILSTYLE_HTML_TH')) define('FORMSLIB_EMAILSTYLE_HTML_TH', 3);

if (!defined('VALIDATE_BACS')) define('VALIDATE_BACS', '/^[a-zA-Z0-9\s&\-\.\/]*$/');
if (!defined('EMAIL_VALIDATE')) define('EMAIL_VALIDATE', '[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?');

if (!defined('POSTCODE_VALIDATE')) define('POSTCODE_VALIDATE', '^[A-PR-UWYZ][A-HK-Y0-9][A-HJKSTUW0-9]?[ABEHMNPRVWXY0-9]? ?[0-9][ABD-HJLN-UW-Z]{2}|GIR 0AA|XM4 5HQ|BFPO [0-9]{1,4}$');
if (!defined('MOBILE_VALIDATE')) define('MOBILE_VALIDATE', '^(\+44|0)7[4-57-9][0-9]{8}$');

require_once 'Formslib.php';
require_once 'form.php';
require_once 'fields.php';
require_once 'custom_fields.php';
require_once 'rules.php';

if (!defined('LIBPATH')) define('LIBPATH', '');
require_once(LIBPATH.'class.JavaScriptPacker.php');

if (version_compare(PHP_VERSION, '5.3.0', '>='))
{
	require_once(__DIR__.'/autoload.php');
}
