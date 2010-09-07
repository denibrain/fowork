<?php

namespace FW\Util\IO;

class FileSystemItem extends \FW\Object {
	
	private $name;
	private $basename = false;

	function __construct($name) {
		$dir = dirname($name);
		if(!is_dir($dir)){
			mkdir($dir, 0644, true);
		}
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