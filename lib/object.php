<?php
namespace FW;

class Object {
	public $tag;
	
	function __set($key, $value) {
		throw new \Exception("Undefined property $key");
	}
	function __get($key) {
		throw new \Exception("Undefined property $key");
	}
}
?>