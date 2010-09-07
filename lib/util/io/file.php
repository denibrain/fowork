<?php

namespace FW\Util\IO;

class File extends FileSystemItem {
	const WRITE = 1;
	const READ = 2;
	const APPEND = 4;
	
	private $handle;
	static private $pathValidator;
	
	function __construct($name, $flags = 0, $path = '') {
		parent::__construct($name);
		if ($path) {
			File::$pathValidator->validate($path);
			if (substr($path, -1) != '/') $path.='/';
			$name = $path.$name;
		}
		if ($flags) {
			$modes = array(0=>'w', 'w', 'r', 'r+', 'a', 'a', 'a+', 'a+');
			$mode = $modes[$flags & 7];
			if (!($this->handle = @fopen($name, $mode)))
				throw new \Exception("Cannot create file $name($mode)");
		}
	}
	
	function __destruct() {
		if (is_resource($this->handle))
			fclose($this->handle);
	}
	
	function getSize($key) {return filesize($this->name);}
	function write($str) {fwrite($this->handle, $str);}
	function writeln($str) {fwrite($this->handle, $str.PHP_EOL);}
	function delete() {\unlink($this->name);}
	function createLink($linkName) {\symlink($this->name, $linkName);}

	static function init() {
		self::$pathValidator = new \FW\Validate\Filename(\FW\Validate\Filename::FULLPATH);
	}
}

File::init();