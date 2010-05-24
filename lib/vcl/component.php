<?php
namespace FW\VCL;

class Component extends \FW\Object {
	
	public $name;
	public $fullname;
	public $params;
	
	
	function __construct($name) {
		$this->name = $name;
		$this->fullname = strtolower(substr(get_class($this), 6).'_'.$name);
		if (isset($_SESSION[$this->fullname])) $this->params = $_SESSION[$this->fullname];
		else $this->params = array();
	}
	
	function display() {
		return E('component');
	}
	
	function __destruct() {
		$_SESSION[$this->fullname] = $this->params;
	}
}

?>