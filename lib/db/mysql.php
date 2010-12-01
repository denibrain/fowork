<?php
namespace FW\DB;

class EMySQL extends EDB {
	public function __construct($link, $query = '') {
		parent::__construct(mysql_error($link)."\n Query: $query", mysql_errno($link));
	}
}

// !mportant in safe mode new link is ignored
class MySQL extends DB {

    public function __construct() {
        parent::__construct();
		
		$this->dbname = 'test';
		$this->user = 'root';
		$this->pass = '';
		$this->port = 3306;

		$this->queryClass = 'FW\DB\MySQLQuery';
    }
    
    function open() {
        if (false===($this->handle = @mysql_connect("$this->host:$this->port", $this->user, $this->pass, true)))
            throw new EDB("Cannot connect DB to $this->host: ". mysql_error());
        if (false===mysql_select_db($this->dbname, $this->handle))
            throw new EMySQL($this->handle);
		
	\mysql_query("SET NAMES '".FW_CHARSET."'");
	$this->connected = true;		
    }

	function close() {
		mysql_close($this->handle);
		$this->connected = false;
	}

	function ping() {
		return mysql_ping($this->handle);
	}

	function reconnect() {
		$this->connected = false;
		$this->open();
	}

	public function begin() {
		if (!$this->level) $this->execute("START TRANSACTION");
		$this->level++;
	}

	public function commit() {
		if (!--$this->level) $this->execute("COMMIT");
	}

	public function rollback() {
		$this->execute("ROLLBACK");
	}
}

class MySQLQuery extends Query {
	
	function __construct(MySQL $db, $query) {
        parent::__construct($db, $query);
		if (false===($this->handle = mysql_query($query, $db->handle))) {
            throw new EMySQL($this->db->handle, $query);
		}
	}
	
	function __destruct() {
		if (is_resource($this->handle))
			if (!@mysql_free_result($this->handle))
				throw new EDB('Invalid query handle $this->handle', 4);
	}

	function seek($row = 0) { mysql_data_seek($this->handle, $row); }
	function getA() { return mysql_fetch_assoc($this->handle);  }
	function get() { return mysql_fetch_row($this->handle);  }
	function count() {return mysql_num_rows($this->handle);  }
	function val() { return (list($v) = mysql_fetch_row($this->handle)) ? $v: false; }
	function id() { return mysql_insert_id($this->db->handle()); }
}
?>