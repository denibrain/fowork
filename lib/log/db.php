<?php
namespace FW\Log;

class DB extends Log {
	
	function __construct($name, $db = false) {
		parent::__construct($name);
		if ($db) {
			if (is_object($db)) $this->handle = $db;
			else $this->handle = DB::connect($db);
		}
		else $this->handle = App::$_->db;
	}
	
	function write($str) {
		$this->handle->execf("INSERT INTO ".FW_TBL_LOG." (createdate, name, message) VALUES (NOW(), :0, :1)", $this->name, $str);
	}
}
