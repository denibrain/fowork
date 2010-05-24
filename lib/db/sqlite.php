<?php
namespace FW;

class DBSqliteException extends DBException {
	public function __construct($link) {
		parent::__construct(sqlite_last_error($link));
	}
}

// !mportant in safe mode new link is ignored
class DBSqlite extends DB {

    public function __construct($dbname = 'test', $dbprefix = '', $user = 'root', $pass = '', 
		$host = 'localhost', $port = 3306) {
        parent::__construct($dbname, $dbprefix, $user, $pass,  $host, $port);

		$this->queryClass = 'DBSqliteQuery';
        if (false===($this->handle = sqlite_open("$host/$dbname", 0666, $err)))
            throw new DBException("Cannot connect DB to $dbname [$err]");
	}
	
    function __destruct() {
        sqlite_close($this->handle);
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

class DBSqliteQuery extends DBQuery {
	function __construct(DBMySQL $db, $query) {
        parent::__construct($db, $query);
		if (false===($this->handle = sqlite_query($db->getHandle(), $query))) {
            throw new DBSqliteException($this->db->getHandle());
		}
	}

    function getA() { return sqlite_fetch_assoc($this->handle);  }
    function get() { return sqlite_fetch_row($this->handle);  }
    function count() {return sqlite_num_rows($this->handle);  }
	function val() { return current(sqlite_fetch_row($this->handle)); }
}
?>