<?php
namespace FW\VCL;

class Controls extends \FW\Object implements \IteratorAggregate, \ArrayAccess {
	private $items = array();
	private $ids = array();

	function __construct() {
		$this->items = array();
		$this->ids = array();
	}

	function __get($key) {
		if (isset($this->items[$key])) return $this->items[$key];
		else return parent::__get($key);
	}
	
	function add($item) {
		if (isset($this->items[$item->name]))
			throw new \Exception("Control {$this->name} already exists");
		$this->items[$item->name] = $item;
		$this->ids[$item->id] = $item;
	}

	function remove($item) {
		unset($this->items[$item->name]);
		unset($this->ids[$item->id]);
	}

	function setOf($className) {
		return \array_filter($this->items, function($value) use ($className){
			return $value instanceof $className;
		});
	}

	function clear() {
		$this->items = array();
		$this->ids = array();
	}

	function getById($id) {
		return isset($this->ids[$id]) ? $this->ids[$id] : null;
	}

	public function offsetExists ($offset) {return isset($this->items[$offset]); }
	public function offsetGet ($offset) { return $this->items[$offset]; }
	public function offsetSet ($offset, $value) { throw new \Exception("Use method add"); }
	public function offsetUnset ($offset) {
		unset($this->ids[$this->items[$offset]->id]);
		unset($this->items[$offset]);
	}
	
	function getIterator() {
		return new \ArrayIterator($this->items);
	}
}
