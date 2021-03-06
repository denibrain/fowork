<?php
namespace FW\App;

class Entity extends \FW\Object {

	protected $app;
	private $classname;
	
	protected $dataset;
	protected $procedure;
	protected $config;

	public function __construct() {
		$this->app = App::$_;

		$this->classname = strtolower(get_class($this));
		
		$self = $this;
		$f = FW_PTH_APP."/db/{$this->classname}.db.php";
		if (file_exists($f)) include $f;
		$f = FW_PTH_ETC."{$this->classname}.cfg.php";
		if (file_exists($f)) include $f;
	}

	function getConfig() { return $this->config; }
	function getClassname() { return $this->classname; }
	function getApp() { return $this->app; }

	function __call($name, $args) {
		$pr = substr($name, 0, 2);
		$funcName = strtolower(substr($name, 2));
		$db = App::$_->db;
		if ($pr == 'ds') {
			if (!isset($this->dataset[$funcName]))
				throw new \Exception("No Dataset $funcName in module $this->classname");
			$ds = $this->dataset[$funcName];
			if (!isset($args[0])) $args[0] = array();
			return  is_string($ds) ?
				$ds = new \FW\DB\DataSet($ds, $args[0])
				:$ds($db, $args[0]);
		}
			
		if ($pr == 'dp') {
			if (!isset($this->procedure[$funcName]))
				throw new \Exception("No Database procedure $funcName in module $this->classname");			
			array_unshift($args, $db);
			$db->begin();
			try {
				$value = call_user_func_array($this->procedure[$funcName], $args);
				$db->commit();
			}
			catch (\Exception $e) {
				$db->rollback();
				throw $e;
			}
			return $value;
		}
		else throw new \Exception("No function $this->classname.$name");
	}	

}
?>