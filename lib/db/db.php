<?php
namespace FW\DB;

class EDB extends \Exception {
	function __construct($message, $code = 2) {
		parent::__construct($message, $code);
	}
}


class DB extends \FW\Object {
	protected $handle;
	protected $prefix;
	protected $queryClass;
	protected $relations;
	protected $quoteChar = '"';
	private $path;

	/*
	connection string : mysql://user:pass@host/database#tableprefix;
	@return: DB
	*/
	static function connect($connectionString = FW_DB, $pathDB = '') {
		$url = @parse_url($connectionString);
		if (!$url && !preg_match('/^[a-z0-9]+$', $url['scheme'])) {
			throw new EDB("Invalid connecton string");
		}
		$class = 'FW\DB\\'.$url['scheme'];
		if (!class_exists($class)) {
			$f = FW_LIB."/db/{$url['scheme']}.php";
			if (!file_exists($f)) throw new EDB("Undefined DB provider {$url['scheme']}");
			require $f;
		}
		
		$dbname  = substr($url['path'], 1);
		if (!preg_match('/^[a-z0-9-_]+$/', $dbname)) {
			throw new EDB("Invalid Database name");
		}
		$provider = new $class(
			$dbname, 
			isset($url['fragment'])?$url['fragment']:'', 
			isset($url['user'])?$url['user']:'', 
			isset($url['pass'])?$url['pass']:'', 
			isset($url['host'])?$url['host']:'localhost', 
			isset($url['port'])?$url['port']:0,
			$pathDB);
		return $provider;
	}
	
	// conver   A => 'A' and A.B => 'A'.'B', where ' is a $this->quoteChar
	public function q($str, $detected = false)  {
		$q = $this->quoteChar;
		if ($detected || false!==strpos($str, '.')) {
			return implode(".", array_map(function($a) use($q) {return $q.$a.$q;}, explode('.', $str)));
		}
		else return $q.$str.$q;
	}	

	function relation($tableA, $tableB, $aA = false, $aB = false) {
		if (isset($this->relations[$tableA]) && isset($this->relations[$tableA][$tableB])) {
			list($a, $b) = $this->relations[$tableA][$tableB];
			$a = $aA ? "$aA.".$this->q("$a") : $this->q("$tableA.$a");
			$b = $aB ? "$aB.".$this->q("$b") : $this->q("$tableA.$b");
			return "$a = $b";
		}
		if (isset($this->relations[$tableB]) && isset($this->relations[$tableB][$tableA])) {
			list($b, $a) = $this->relations[$tableB][$tableA];
			$a = $aA ? "$aA.".$this->q("$a") : $this->q("$tableA.$a");
			$b = $aB ? "$aB.".$this->q("$b") : $this->q("$tableA.$b");
			return "$a = $b";
		}
		return false;
	}

	public function proceedValue($value, $q = "'") {
		if (is_null($value)) return 'NULL';
		if (is_int($value) or is_float($value)) return $value;
		if (is_bool($value)) return $value?'TRUE':'FALSE';
		if (is_string($value)) {
			return $q.str_replace(array("\\", $q), array("\\\\", "\\".$q), $value).$q;
		}
		if (is_array($value)) {
			foreach ($value as &$item) $item = $this->proceedValue($item);
			return implode(", ", $value);
		}
		return $q.$q;
	}
	
	public function __construct($dbname, $dbprefix, $user, $pass, $host, $port, $path) {
		$this->path = $path;
		$this->prefix = $dbprefix;
		if (file_exists($f = $path.'relations.php')) include $f;
	}
	
	public function __get($key) {
		switch ($key) {
			case 'handle': return $this->handle;
			case 'path': return $this->path;
			case 'prefix' : return $this->prefix;
			default:
				parent::__get($key);
		}
	}
	
	public function __invoke() {
		return $this->execute(call_user_func_array(array($this, 'format'), func_get_args()));
	}

	public function execute($query) {
		return new $this->queryClass($this, $query);
	}
	
	public function format() {
		$parameters = func_get_args();
		if (!count($parameters)) throw new EDB('Too few parametrs');
		$format = array_shift($parameters);
		$parameters['~@'] = $this->prefix;
		$n = 0; $self = $this;
		return preg_replace_callback(array('/:(\?|[0-9]+)/', '/(~@)/'),
			function ($regs) use ($parameters, &$n, $self) {
				if ($regs[1] == '?') {
					$regs[1] = $n;
					++$n;
				}
				return $self->proceedValue($parameters[$regs[1]]);
			}, $format);
	}

	public function transaction($func) {
		$this->begin();
		try {
			$func($this); 
		}
		catch (Exception $e) {
			$this->rollback();
			throw $e;
		}
		$this->commit();
	}

	public function __call($name, $args) {
		switch ($name) {
			case 'getA':
			case 'get':
			case 'val':
			case 'execf':
				$q = call_user_func_array(array($this, '__invoke'), $args);
				if ($name == 'execf') return $q;
				return $q->$name();
			default:
				throw new EDB('Not exists function '.$name);
		}
	}
	
	/* abstrat methods, must overidded on derive class */
	public function begin() {}
	public function rollback() {}
	public function commit() {}
}

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