<?php

namespace FW\IO;

class File extends FileSystemItem {
	const CREATE = 8;
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
		if ($flags) $this->open($flags);
	}

	function open($mode) {
		$modes = array('w', 'r', 'r+', 'a', 'a', 'a+', 'a+');
		$mode = $modes[$mode & 7];
		if (!($this->handle = @fopen($this->name, $mode)))
			throw new \Exception("Cannot create file $this->name($mode)");
	}

	function close() {
		if (is_resource($this->handle)) {
			fclose($this->handle);
			$this->handle = NULL;
		}
	}

	function __destruct() {
		if (is_resource($this->handle))
			fclose($this->handle);
	}
	
	function getSize($key) {return filesize($this->name);}

	function write($str) {
		if (!$this->handle) {
			$this->open(File::WRITE);
			$this->lock(File::WRITE);
		}
		fwrite($this->handle, $str);
	}
	
	function writeln($str) {fwrite($this->handle, $str.PHP_EOL);}

	function delete() {
		if (!\unlink($this->name))
		throw new Exception("Cannot delete $this->name file");
	}

	function createLink($linkName) {
		if (!\symlink($this->name, $linkName))
		throw new Exception("Cannot create link $linkName fo $this->name file");
	}

	function linkTo($targetName) {
		if (!file_exists($targetName))
		throw new Exception("$targetkName not found");
		if (!\symlink($linkName, $this->name))
		throw new Exception("Cannot create link $this->name for $targetName file");
	}

	static function init() {
		self::$pathValidator = new \FW\Validate\Filename(\FW\Validate\Filename::FULLPATH);
	}
}

File::init();