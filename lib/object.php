<?php
namespace FW;

class Object {
	public $tag;
	
	function __set($key, $value) {
		if (method_exists($this, $m = 'set'.$key)) {
			$this->$m($value);
			return $value;
		}
		throw new \Exception("Undefined property $key");
	}
	function __get($key) {
		if (method_exists($this, $m = 'get'.$key)) return $this->$m();
		throw new \Exception("Undefined property $key");
	}
}
?>