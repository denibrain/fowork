<?php
namespace FW\App;

class Cell extends Entity {
	
	protected $id;
	protected $fields;
	
	public function __construct($id = false) {
		if (is_array($id)) $this->set($id);
		if ($id) $this->select($id);

		$this->app = App::$_;
		$this->classname = strtolower(get_class($this));
		
		$f = FW_PTH_APP."{$this->classname}.db.php";
		if (file_exists($f)) include $f;
	}
	
	public function __get($key) {
		if ($key == 'id') return $this->id;
		elseif (isset($this->fields[$key])) $this->fields[$key];
		else parent::__get($key);
	}
	
	public function __set($key, $value) {
		if ($key == 'id') return $this->setId($id);
		elseif (isset($this->fields[$key])) return $this->fields[$key] = $key;
		else parent::__set($key, $value);
	}

	function select($id) {
		$this->fields = $this->dsSelect(array('id'=>$id))->getA();
	}

}
?>