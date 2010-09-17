<?php
namespace FW;

class DBMSSQLException extends DBException {
	public function __construct($link) {
		parent::__construct(get_last_message ($link), 0);
	}
}

// !mportant in safe mode new link is ignored
class DBMSSQL extends DB {

    public function __construct($dbname = 'test', $dbprefix = '', $user = 'root', $pass = '', 
		$host = 'localhost', $port = 3306) {
        parent::__construct($dbname, $dbprefix, $user, $pass,  $host, $port);

		$this->queryClass = 'DBMSSQLQuery';
		
        if (false===($this->handle = mssql_connect("$host:$port", $user, $pass, true)))
            throw new DBException("Cannot connect DB to $host");
        if (false===mssql_select_db($dbname, $this->handle))
            throw new DBMySQLException($this->handle);
    }

    function __destruct() {
        mssql_close($this->handle);
    }
	
	public function transaction() {
        $queries = func_get_args();
		$this->execute("START TRANSACTION");
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
}

class DBMSSQLQuery extends DBQuery {
	function __construct(DBMySQL $db, $query) {
        parent::__construct($db, $query);
		if (false===($this->handle = mssql_query($query, $db->getHandle()))) {
            throw new DBMSSQLException($this->db->getHandle());
		}
	}

	function getA() { return mssql_fetch_assoc($this->handle);  }
	function get() { return mssql_fetch_row($this->handle);  }
	function count() {return mssql_num_rows($this->handle);  }
	function val() { return current(mssql_fetch_row($this->handle)); }
}
?>