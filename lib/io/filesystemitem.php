<?php

namespace FW\IO;

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

	function getName() {return $this->name;}
	function getExists() { return file_exists($this->name);}
	function getBasename() {
		return false === $this->basename ?
			$this->basename = \basename($this->name) :
			$this->basename;
	}
	
	function setName($value) {
		return rename($this->name, $value);
	}
	
	function delete() {}
} 