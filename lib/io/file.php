<?php

namespace FW\IO;

class File extends FileSystemItem {
	const WRITE = 1;
	const CREATE = 2;
	const EXCL = 8;
	const READ = 4;
	
	private $handle;
	private $locked = false;
	static private $pathValidator;

	public function getOpened() { return !!$this->handle && is_resource($this->handle); }

	function __construct($name, $flags = false, $path = '') {
		parent::__construct($name);
		if ($path) {
			File::$pathValidator->validate($path);
			if (substr($path, -1) != '/') $path.='/';
			$name = $path.$name;
		}
		if ($flags!==false) $this->open($flags);
	}

	function open($mode = 0) {
		$modes = array(1 => 'a', 'w',  'w',
					  'r', 'r+', 'w+', 'w+'.
					  'x', 'x',  'x',  'x',
					  'x+','x+', 'x+', 'x+');

		$mode = $modes[$mode & 15];
		if (!($this->handle = @fopen($this->name, $mode)))
			throw new \Exception("Cannot create file $this->name($mode)");
	}

	function close() {
		if ($this->opened) {
			if ($this->locked) $this->unlock();
			fclose($this->handle);
			$this->handle = NULL;
		}
	}

	function __destruct() {	if ($this->opened) $this->close();	}
	function getSize($key) {return filesize($this->name);}

	function write($str) {
		if (!$this->handle) {
			$this->open(File::CREATE | File::WRITE);
			$this->lock(File::WRITE);
		}
		\fwrite($this->handle, $str);
	}
	
	function writeln($str) {
		if (!$this->handle) {
			$this->open(File::CREATE | File::WRITE);
			$this->lock(File::WRITE);
		}
		\fwrite($this->handle, $str.PHP_EOL);
	}

	function read($max = 2048) {
		if (!$this->handle) $this->open(File::READ);
		return \fread($this->handle, $max);
	}

	function readln() {
		if (!$this->handle) $this->open(File::READ);
		return \fgets($this->handle);
	}

	function delete() {
		if ($this->exists && !\unlink($this->name))
		throw new \Exception("Cannot delete $this->name file");
	}

	function createLink($linkName) {
		if (!\symlink($this->name, $linkName))
		throw new \Exception("Cannot create link $linkName fo $this->name file");
	}

	function linkTo($targetName) {
		if (!file_exists($targetName))
		throw new \Exception("$targetName not found");
		if (!\symlink($targetName, $this->name))
		throw new \Exception("Cannot create link $this->name for $targetName file");
	}

	function lock($mode) {
		$mode = $mode == File::READ ? LOCK_SH : LOCK_EX;
		flock($this->handle, $mode);
		$this->locked = true;
	}

	function unlock() {
		flock($this->handle, LOCK_UN);
		$this->locked = false;
	}

	static function init() {
		self::$pathValidator = new \FW\Validate\Filename(\FW\Validate\Filename::FULLPATH);
	}

	function copyTo($name) {
		$f = F($name);
		if ($f->exists) $f->delete();
		copy($this->name, $name);
	}
}

File::init();