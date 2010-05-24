<?php
namespace FW\DB;

class PgSQLException extends EDB {
	public function __construct($link) {
		parent::__construct(pg_last_error($link));
	}
}

// !mportant in safe mode new link is ignored
class PgSQL extends DB {

    public function __construct($dbname = 'test', $dbprefix = '', $user = 'root', $pass = '', 
		$host = 'localhost', $port = 5432, $path) {
        parent::__construct($dbname, $dbprefix, $user, $pass,  $host, $port, $path);

		if (!$port) $port = 5432;
		
		$this->queryClass = 'FW\DB\PgSQLQuery';
        if (false===($this->handle = \pg_connect("dbname=$dbname host=$host port=$port user=$user password=$pass")))
            throw new EDB("Cannot connect DB to $host");
			
		\pg_query("SET NAMES '".FW_CHARSET."'");
	}
	
    function __destruct() {
        pg_close($this->handle);
    }
	
	public function transaction() {
        $queries = func_get_args();
		$this->execute("BEGIN");
		foreach($queries as $query) {
			try {
				$this->execute($queries); 
			}
			catch (Exception $e) {
				$this->execute("ROLLBACK");
				throw $e;
			}
		}
		$this->execute("COMMIT");
	}
	
	function begin() {
		$this->execute("BEGIN");
	}
	
	function commit() {
		$this->execute("COMMIT");
	}
	
	function rollback() {
		$this->execute("ROLLBACK");
	}
	
	public function proceedValue($value, $q = "'") {
		if (is_array($value)) {
			foreach ($value as &$item) $item = $this->proceedValue($item, '"');
			$value = "{".implode(", ", $value)."}";
			return $q == "'" ? "'$value'" : $value;
		}
		return parent::proceedValue($value, $q);
	}	
}

class PgSQLQuery extends Query {
	function __construct(PgSQL $db, $query) {
        parent::__construct($db, $query);
		if (false===($this->handle = @\pg_query($db->handle, $query))) {
			echo $query;
            throw new PgSQLException($this->db->handle);
		}
	}

    function getA() { return \pg_fetch_assoc($this->handle);  }
    function get() { return \pg_fetch_row($this->handle);  }
    function count() {return \pg_num_rows($this->handle);  }
	function val() {
		$d = \pg_fetch_row($this->handle);
		return $d ? current($d) : false; }
}
?>