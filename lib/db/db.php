<?php
namespace FW\DB;

class DB extends \FW\Object {
	protected $handle;
	protected $prefix;
	protected $queryClass;
	protected $relations;
	protected $quoteChar = '"';
	private $path = 'db/';
	
	protected $port = 0;
	protected $host = 'localhost';
	protected $user = 'guest';
	protected $pass = '';
	protected $dbname = 'test';
	protected $connected = false;

	/*
	connection string : mysql://user:pass@host/database#tableprefix;
	@return: DB
	*/
	static function connect($connectionString = FW_DB) {
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
		$provider = new $class();
		
		$provider->dbname = $dbname;
		if(isset($url['fragment'])) $provider->prefix = $url['fragment'];
		if(isset($url['user'])) $provider->user = $url['user'];
		if(isset($url['pass'])) $provider->pass = $url['pass'];
		if(isset($url['host'])) $provider->host = $url['host']; 
		if(isset($url['port'])) $provider->port = $url['port'];

		$provider->open();
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
	
	public function __construct() {
		$this->path = defined('FW_PTH_DB') ? FW_PTH_DB : 'db/';
		$this->prefix = '';
		$this->host = 'localhost';
		$this->user = 'guest';
		$this->pass = '';
		$this->port = 0;
		$this->connected = false;
	}
	
    function __destruct() {
		if ($this->connected) $this->close();
    }
	
	public function open() {
		if (file_exists($f = $this->path.'relations.php')) include $f;
	}
	
	public function close() {
		
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
		return preg_replace_callback(array('/:(\?|#([0-9]+))/', '/(~@)/'),
			function ($regs) use ($parameters, &$n, $self) {
				if ($regs[1] == '?') {
					$regs[1] = $n;
					++$n;
				}
				elseif (isset($regs[2])) $regs[1] = $regs[2];
				
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

class EDB extends \Exception {
	function __construct($message, $code = 2) {
		parent::__construct($message, $code);
	}
}
?>