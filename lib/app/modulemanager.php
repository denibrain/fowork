<?php
namespace FW\App;

class ModuleManager extends \FW\Object implements \IteratorAggregate {
	
	private $path = array();
	private $cache;
	private $app;

	function getIterator() {
		return new \ArrayIterator($this->cache);
	}

	function __construct($app, $path = '') {
		$this->app = $app;
		$this->cache = array();
		$this->path[0] = $path?$path:FW_PTH_APP.'modules/';
		spl_autoload_register(array($this, 'load'));
	}
	
	function addPath($path) {
		$this->path[$path] = $path;
	}

	function __get($key) {
		$key = strtolower($key);
		if (isset($this->cache[$key])) return $this->cache[$key];

		if (false!==($sep = strpos($key, '_'))) {
			$classname = $key;
			$classname[$sep] = '\\';
			$filename = substr($classname, $sep + 1);
		}
		else {
			$filename = $key;
			$classname = '\\'.$key;
		}
		if (!class_exists($classname)) {
			foreach($this->path as $path) {
				$modulfile = $path.$filename.'.php';
				if (file_exists($modulfile)) {
					require_once $modulfile;
					$cls = new $classname($this->app);
					return $this->cache[$key] = $cls;
				}
				
			}
			throw new EApp("Module $key not found ($modulfile)");
		}
		return $this->cache[$key] = new $classname($this->app);
	}

	function load($name) {
		foreach($this->path as $path) {
			$modulfile = strtolower($path.$name.'.php');
			if (file_exists($modulfile)) {
				require_once $modulfile;
				return;
			}
		}
		//throw new \Exception("Module $name not found!");
	}
}
