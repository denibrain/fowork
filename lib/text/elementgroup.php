<?php
namespace FW\Text;

class ElementGroup extends \FW\Object implements \IteratorAggregate, \ArrayAccess {

	private $name;
	private $items;
	private $count;
	private $parentNode;
	
	public function offsetExists($offset) { return isset($this->items[$offset]); }
	public function offsetGet($offset) { return $this->items[$offset]; }
	public function offsetSet($offset, $e) {
		$e->parentNode = $this->parentNode;
		if (!isset($this->items[$offset])) ++$this->count;
		$this->items[$offset] = $e;
	}

	public function offsetUnset ($offset) {
		if (isset($this->items[$offset])) --$this->count;
		unset($this->items[$offset]);
	}

	public function getIterator() {
		return new \ArrayIterator($this->items);
	}

	function __construct($mixed, $parent) {
		$this->name = is_string($mixed)?$mixed:$mixed->tag;
		if (is_object($mixed)) $this->add($mixed);
		$this->parentNode = $parent;
		$this->count = 0;
	}

	function __get($key) {
		switch($key) {
			case 'name': return $this->name;
			case 'count': return $this->count;
			default: return parent::__get($key);
		}
	}

	function asXML() {
		$text = '';
		foreach($this->items as $i) $text.= $i->asXML();
		return $text;
	}

	function add($e) {
		$this->items[] = $e;
		$e->parentNode = $this->parentNode;
		++$this->count;
	}
}
?>
