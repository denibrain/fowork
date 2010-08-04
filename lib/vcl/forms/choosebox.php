<?php
namespace FW\VCL\Forms;


class ChooseBox extends Field implements \ArrayAccess, \IteratorAggregate {
	private $options = array();

	public function offsetExists($offset) { return isset($this->options[$offset]); }
	public function offsetGet($offset) { return $this->options[$offset]; }
	public function offsetSet($offset, $value) { $this->options[$offset] = $value; }
	public function offsetUnset($offset) { unset($this->options[$offset]); }
	public function getIterator() { return new ArrayIterator($this->options); }
	
	function validate($value) {
		if (!isset($this->options[$value]))
			throw new EFormData('FFLIST.invalidkey', $this->name);
	}

	function __get($key) {
		switch ($key) {
			case 'options': return $this->options;
				break;
			default:
				return parent::__get($key);
		}
	}
	
	function __set($key, $value) {
		switch ($key) {
			case 'options': $this->options = $value;
				break;
			default:
				parent::__set($key, $value);
		}
	}
	
	function display() {
		$e = parent::display();
		foreach($this->options as $key=>$value) {
			$e->add(E('option', A('value', $key, 'caption', $value)));
		}
		return $e;
	}
}
