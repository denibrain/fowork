<?php
namespace FW\VCL\Forms;

class FormFields extends \FW\Object implements \IteratorAggregate, \ArrayAccess {
	private $items = array();

	function __get($key) {
		if (isset($this->items[$key])) return $this->items[$key];
		else return parent::__get($key);
	}
	
	function add($item) {
		if (isset($this->items[$item->name]))
			throw new \Exception("Field {$this->name} already exists");
		$this->items[$item->name] = $item;
	}
	
	public function offsetExists ($offset) {return isset($this->items[$offset]); }
	public function offsetGet ($offset) { return $this->items[$offset]; }
	public function offsetSet ($offset, $value) { throw new \Exception("Use method add"); }
	public function offsetUnset ($offset) { unset($this->items[$offset]); }
	
	function getIterator() {
		return new \ArrayIterator($this->items);
	}
}
