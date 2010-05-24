<?php
namespace FW;

class DBMySQLiException extends DBException {
	public function __construct($link) {
		parent::__construct(mysqli_error($link), mysqli_errno($link));
	}
}

class DBMySQLi extends DB {

    public function __construct($dbname = 'test', $dbprefix = '', $user = 'root', $pass = '',  $host = 'localhost', $port = 3306) {
        parent::__construct($dbname, $dbprefix, $user, $pass,  $host, $port);
        if (false===$this->handle = mysqli_connect($host, $user, $host, $dbname, $port))
            throw new DBException("Cannot connect DB to $host");
    }

    function __destruct() {
        mysqli_close($this->handle);
    }
}

class DBMySQLiQuery extends DBQuery {
	function __construct(DBMySQLi $db, $query) {
        parent::__construct($db, $query);
		if (false===($this->handle = mysqli_query($db->getHandle(), $query))) {
			throw new DBMySQLiException($this->db->getHandle());
		}
	}

	function getA() { return mysqli_fetch_assoc($this->handle);  }
	function get() { return mysqli_fetch_row($this->handle);  }
	function val() { return (list($v) = mysqli_fetch_row($this->handle)))?$v:false; }
	function count() {return mysqli_num_rows($this->handle);  }
}
?>