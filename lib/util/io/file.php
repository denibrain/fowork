<?php

namespace FW\Util\IO;

class File extends FileSystemItem {
	private $handle;
	
	function __construct($name, $flags = 0, $path = '') {
		parent::__construct($name);
		if ($path) {
			Validator::validate($path, 'path');
			if (substr($path, -1) != '/') $path.='/';
			$name = $path.$name;
		}
		if ($flags) {
			$modes = array(1=>'w', 'r', 'r+', 'a', 'a', 'a+', 'a+');
			$mode = $modes[$flags & 7];
			if (!($this->handle = @fopen($name, $mode)))
				throw new \Exception("Cannot create file $name($mode)");
		}
	}
	
	function __destruct() {
		if (is_resource($this->handle))
			fclose($this->handle);
	}
	
	function __get($key) {
		switch($key) {
			case 'size' : return filesize($this->name);
			default: return parent::__get($key);
		}
	}
	
	function write($str) {
		fwrite($this->handle, $str);
	}

	function writeln($str) {
		fwrite($this->handle, $str.PHP_EOL);
	}
	
	function delete() {
		\unlink($this->name);
	}
	
	function createLink($linkName) {
		\symlink($this->name, $linkName);
	}
}