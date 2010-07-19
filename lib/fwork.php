<?php
namespace FW;

ini_set('display_errors', 'On');
ini_set('error_reporting', E_ALL);

if (!ini_get('date.timezone')) {
	ini_set('date.timezone', 'Asia/Yekaterinburg');
}

if (!defined('FW_LIB')) define('FW_LIB', dirname(__FILE__).'/');


spl_autoload_register(function ($name) {
	if (substr($name, 0, 3) == 'FW\\') {
		if (!file_exists($f = FW_LIB.strtolower(strtr(substr($name, 3), '\\', '/')).'.php'))
			throw new \Exception("Class $name not found! [$f]");
		require $f; 
	}
});

require FW_LIB."shortcuts.php";
?>