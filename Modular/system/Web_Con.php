<?php
@header("Content-type: text/html; charset=utf-8");
ini_set("display_errors", "On");
error_reporting(E_ALL | E_STRICT);
date_default_timezone_set("PRC");
define('D', DIRECTORY_SEPARATOR);
define('P', dirname(dirname(dirname(__FILE__))) . D);
define('MP', P . 'Modular' . D);
define('SP', MP . 'system' . D);
require_once SP . "appclass" . D . "classload.php";
system\Classload::loadConfig();
spl_autoload_register('\\system\\Classload::autoload');