<?php
namespace FW\DB;

class Query implements \Iterator {
	protected $db;
	protected $handle = false;
	protected $row = 0;
	protected $data = array();

	public function __construct(DB $db, $query) {
		$this->row = 0;
		$this->db = $db;
	}

	public function key() { return $this->row; }
	public function valid() { return false !== ($this->data = $this->getA());}
	public function rewind() { if ($this->row) $this->seek(0); }
	public function next() { $this->row++; }
	public function current() { return $this->data; }

	public function dic() {
		$list = array();
		while (list($key, $value) = $this->get()) $list[$key] = $value;
		return $list;
	}

	public function lst() {
		$list = array();
		while (list($value) = $this->get()) $list[] = $value;
		return $list;
	}
	
	public function items($mixed = '', $itemName = 'item', $mapper = NULL) {
		$c = 'FW\Text\Element';
		if ($mixed instanceof $c) $e = $mixed; else $e = E($mixed);
		if (isset($mapper))
		foreach($this as $item) $mapper($e->add(E($itemName, $item)));
		else 
		foreach($this as $item) $e->add(E($itemName, $item));
		return $e;
	}

	// abstract methods
	public function val() {}
	public function seek($row = 0) {}
	public function getA() {}
	public function get() {}
	public function count() {}
	public function id() {}
}
?>