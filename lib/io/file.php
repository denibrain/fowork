<?php

namespace FW\IO;

class File extends FileSystemItem implements \Iterator {
	const WRITE = 1;
	const CREATE = 2;
	const EXCL = 8;
	const READ = 4;
	
	private $handle;
	private $buffer;
	private $bufferSize;
	private $bufferPointer;
	private $lineNo = 0;
	private $data;
	private $locked = false;
	static private $pathValidator;

	public function getOpened() { return !!$this->handle && is_resource($this->handle); }
	public function getSize($key) {return filesize($this->name);}

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

		$this->buffer = '';
		$this->bufferSize = 0;
		$this->bufferPointer = 0;
	}

	function close() {
		if ($this->opened) {
			if ($this->locked) $this->unlock();
			fclose($this->handle);
			$this->handle = NULL;
		}
	}

	function __destruct() {	if ($this->opened) $this->close();	}

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

	private function loadBuffer($size) {
		if (feof($this->handle)) return false;
		$this->buffer = \fread($this->handle, $size);
		$this->bufferSize = strlen($this->buffer);
		$this->bufferPointer = 0;
		if (!$this->bufferSize) return false;
		return true;
	}

	function readln($lineMax = 2048) {
		if (!$this->handle) $this->open(File::READ);

		if ($this->bufferPointer == $this->bufferSize) {
			if (!$this->loadBuffer($lineMax << 1)) return false;
		}

		$pos = \strpos($this->buffer, "\n", $this->bufferPointer);
		$data = '';
		if (false!==$pos) {
			$data = \substr($this->buffer, $this->bufferPointer, $pos - $this->bufferPointer);
			$this->bufferPointer = $pos + 1;
		} else {
			$data = \substr($this->buffer, $this->bufferPointer);
			$this->bufferPointer = $this->bufferSize;
			if ($this->loadBuffer($lineMax << 1)) {
				$pos = \strpos($this->buffer, "\n", $this->bufferPointer);
				if ($pos === false) {
					$data .= $this->buffer;
					$this->bufferPointer = $this->bufferSize;
				} else {
					$data .= \substr($this->buffer, 0, $pos);
					$this->bufferPointer = $pos + 1;
				}
			}
			if (strlen($data) > $lineMax)
				throw new \Exception ('Line is too long');
		}
		return \rtrim($data, "\r\n");
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

	function backup() {
		if (!@copy($this->name, $this->name.'.bak'))
			throw new \Exception("Cannot create backup");
	}

	function restore() {
		if (!file_exists($this->name.'.bak'))
			throw new \Exception("Backup not found");
		if (!@copy($this->name.'.bak', $this->name))
			throw new \Exception("Cannot restore from backup");
		if (!\unlink($this->name.'.bak'))
			throw new \Exception("Cannot delete backup");


	}

	public function key() { return $this->lineNo;}

	public function valid() {
	    return false!== $this->data = $this->readln();
	}

	public function rewind() {
		if ($this->lineNo) {
			$this->lineNo = 0;
			\fseek($this->handle, 0);
		}
	}

	public function next() {
	    $this->lineNo++;
	}

	public function current() {
		return $this->data;
	}

	public function setName($newName) {
		if (file_exists($newName) && !@\unlink($newName))
			throw new \Exception("Cannot delete $newName file");
		if (!@\rename($this->name, $newName))
			throw new \Exception("Cannot rename file");
		$this->name = $newName;
	}
}

File::init();