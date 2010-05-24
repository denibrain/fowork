<?php
namespace FW;

define('FW_FM_WRITE', 1);
define('FW_FM_READ', 2);
define('FW_FM_APEND', 4);

class FileSystemItem extends Object {
	private $name;
	private $basename = false;

	function __construct($name) {
		$this->name = $name;
	}

	function __toString() {
		return $this->name;
	}

	function __get($key) {
		switch($key) {
			case 'name' : return $this->name;
			case 'basename' : return false === $this->basename ? $this->basename = \basename($this->name) : $this->basename;
			case 'exists' : return file_exists($this->name);
			default: return parent::__get($key);
		}
	}
	
	function __set($key, $value) {
		switch($key) {
			case 'name' : return rename($this->name, $value);
			default:
				parent::__set($key, $value);
		}
	}
	
	function delete() {}
} 

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

class Dir extends FileSystemItem implements \Iterator {
	private $data;
	private $dir = 0;
	private $no = 0;

	function __destruct() {
	    if ($this->dir) $this->dir->close();
	}

	function create($mode = 0755, $user = '') {
		if ($this->exists) 
			chmod($this->name, $mode);
		else
			mkdir($this->name, $mode, true);
		if ($user) chown($this->name, $user);
	}
	
	function delete() {
		if (!$this->exists) throw new \Exception("Directory $this->name not exists!");
		foreach($this as $item) 
			$item->delete();
		\rmdir($this->name);
	}
	
	function deleteFiles($mask, $recursive = false) {
		if (!$this->exists) throw new \Exception("Directory $this->name not exists!");
		foreach($this as $item) { 
			if (preg_match($mask, $item->basename)) $item->delete();
			elseif ($recursive && $item instanceof Dir) $item->deleteFiles($mask, true);
		}
	}
	
	public function key() { return $this->no; }
	public function valid() { 
	    do {
		$this->data = $this->dir->read();
	    } while ($this->data && ($this->data == '.' || $this->data == '..'));
	    return false !== ($this->data);
	}
	public function rewind() { 
		$this->no = 0;
		if(file_exists($this->name)) {
			if (!$this->dir)  
				$this->dir = \dir($this->name);  
			else 
				$this->dir->rewind(); 
		}
	}
	public function next() { 
	    $this->no++; 
	}
	public function current() { 
		$name = "$this->name/$this->data";
		return is_dir($name) ? new Dir($name) : new File($name);
	}
}

?>