<?php
namespace FW\Log;

class File extends Log {
	
	function __construct($name, $path = false) {
		parent::__construct($name);
		if (!$path) $path = FW_PTH_LOG;
		$this->handle = new \FW\IO\File("$name.log", \FW\IO\File::WRITE, $path);
	}
	
	function write($str) {
		$this->handle->write(date('[Y/m/d H:i:s] ').str_replace("\n", "\n\t", $str)."\n\n");
	}
}