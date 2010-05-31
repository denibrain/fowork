<?php
namespace FW\DB;

// !mportant in safe mode new link is ignored
class PgSQL extends DB {

    public function __construct() {
        parent::__construct();

		$this->user = 'postgres';
		$this->port = 5432;
		$this->queryClass = 'FW\DB\PgSQLQuery';
	}
	
	function open() {
		parent::open();
        if (false===($this->handle = \pg_connect("dbname=$this->dbname host=$this->host port=$this->port user=$this->user password=$this->pass")))
            throw new EDB("Cannot connect DB to $host");
			
		\pg_query("SET NAMES '".FW_CHARSET."'");
		$this->connected = true;
	}

	function close() {
        pg_close($this->handle);
		$this->connected = false;
	}
	
	function begin() {
		$this->execute("START TRANSACTION");
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

class PgSQLException extends EDB {
	public function __construct($link) {
		parent::__construct(pg_last_error($link));
	}
}

?>