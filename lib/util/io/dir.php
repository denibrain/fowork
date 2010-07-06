<?php

namespace FW\Util\IO;

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