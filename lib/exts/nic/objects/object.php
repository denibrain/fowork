<?php
namespace FW\Exts\Nic\Objects;

/**
 * Description of object
 *
 * @author d.russkih
 */
class Object {
	function getAsMagicArray() {
		$a = array();
		foreach(get_object_vars($this) as $key => $item) if ($item !== NULL){
			$a[preg_replace_callback('/[A-Z]/',
				function($m) { return "-".strtolower($m[0]);}, $key)] = $item;
		}
		return $a;
	}

	function loadFromMagicArray($array) {
		$a = array();
		foreach(get_object_vars($this) as $key => $item) {
			$key = preg_replace_callback('/-([a-z])/',
				function($m) { return strtoupper($m[1]);}, $key);
			if (\property_exists($this, $key)) $this->$key = $item;
		}
		return $a;
	}
}