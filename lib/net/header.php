<?php
namespace FW\Net;

class HeaderLine extends \FW\Object {
	private $name;
	private $value;
	private $properties;
	
	function __construct($name, $value = '') {
		$this->name = $name;
		$this->__set('value', $value);
		$this->properties = array();
	}

	function __set($key, $value) {
		if ($key=='value') $this->value = remle($value);
		else $this->properties[$key] = remle((string)$value);
	}

	function __get($key) {
		if ($key=='value') return $this->value;
		elseif (isset($this->properties[$key])) return $this->properties[$key];
		else return parent::__set($key, $value);
	}

	function property($key, $value) {
		$this->properties[$key] = remle($value);
		return $this;
	}
	
	function __toString() {
		$text = "$this->name: ". T($this->value)->qencoded;
		foreach($this->properties as $key => $value) {
			$text.= ";".PHP_EOL."\t$key=\"".T($value)->qencoded."\"";
		}
		return $text.PHP_EOL;
	}
}

class Header extends \FW\Object {
	private $items;
	
	function __construct() {
		$this->items = array();
	}
	
	function __set($key, $value) {
		$key = str_replace('_', '-', $key);
		$this->items[$key] = new HeaderLine($key, (string)$value);
	}

	function __get($key) {
		$key = str_replace('_', '-', $key);
		if (isset($this->items[$key])) return $this->items[$key];
		else return parent::__set($key, $value);
	}
	
	function prepend() {
		$a = func_get_args();
		$key = $a[0];
		if ($key instanceof Header) {
			$this->items = array_merge($key->items, $this->items);
		} else {
			$value = $a[1];
			$line = new MailHeaderLine($key, (string)$value);
			array_unshift($this->items, $a);
			return $line;
		}
	}
	
	function add() {
		$a = func_get_args();
		$key = $a[0];
		if ($key instanceof Header) {
			$this->items = array_merge($this->items, $key->items);
		} else {
			if (!isset($a[1])) echo ("No value for headerline $key");
			$value = $a[1];
			$line = $this->items[$key] = new HeaderLine($key, (string)$value);
			return $line;
		}
	}
	
	function __toString() {
		return implode('', $this->items).PHP_EOL;
	}
	
	function addDate() {
		$tz = date('Z');
		$tzs = ($tz < 0) ? '-' : '+';
		$tz = abs($tz);
		$tz = (int)($tz/3600)*100 + ($tz%3600)/60;
		$result = sprintf("%s %s%04d", date('D, j M Y H:i:s'), $tzs, $tz);

		$this->Date = $result;
	}
}
?>