<?php
namespace FW;

if (!defined('FW_LIB')) define('FW_LIB', dirname(__FILE__).'/');

spl_autoload_register(function ($name) {
	if (substr($name, 0, 3) == 'FW\\') {
		if (!file_exists($f = FW_LIB.strtolower(strtr(substr($name, 3), '\\', '/')).'.php'))
			throw new \Exception("Module $name not found! [$f]");
		require $f; 
	}
});

require FW_LIB."shortcuts.php";
?>