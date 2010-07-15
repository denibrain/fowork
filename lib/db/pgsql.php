<?php
namespace FW\DB;

// !mportant in safe mode new link is ignored
class PgSQL extends DB {

	private $level;
	
	protected $reservedWords = array(
		'ANALYSE'=>1, 'ANALYZE'=>1, 'AND'=>1, 'ANY'=>1, 'ARRAY'=>1, 'AS'=>1,
		'ASC'=>1, 'ASYMMETRIC'=>1, 'BOTH'=>1, 'CASE'=>1, 'CAST'=>1, 'CHECK'=>1,
		'COLLATE'=>1, 'COLUMN'=>1, 'CONSTRAINT'=>1, 'CREATE'=>1, 'CURRENT_DATE'=>1,
		'CURRENT_ROLE'=>1, 'CURRENT_TIME'=>1, 'CURRENT_TIMESTAMP'=>1, 'CURRENT_USER'=>1,
		'DEFAULT'=>1, 'DEFERRABLE'=>1, 'DESC'=>1, 'DISTINCT'=>1, 'DO'=>1, 'ELSE'=>1,
		'END'=>1, 'EXCEPT'=>1, 'FALSE'=>1, 'FOR'=>1, 'FOREIGN'=>1, 'FROM'=>1, 'GRANT'=>1,
		'GROUP'=>1, 'HAVING'=>1, 'IN'=>1, 'INITIALLY'=>1, 'INTERSECT'=>1, 'INTO'=>1,
		'LEADING'=>1, 'LIMIT'=>1, 'LOCALTIME'=>1, 'LOCALTIMESTAMP'=>1, 'NEW'=>1,
		'NOT'=>1, 'NULL'=>1, 'OFF'=>1, 'OFFSET'=>1,	'OLD'=>1, 'ON'=>1, 'ONLY'=>1,
		'OR'=>1, 'ORDER'=>1, 'PLACING'=>1, 'PRIMARY'=>1, 'REFERENCES'=>1, 'SELECT'=>1,
		'SESSION_USER'=>1, 'SOME'=>1, 'SYMMETRIC'=>1, 'TABLE'=>1, 'THEN'=>1, 'TO'=>1,
		'TRAILING'=>1, 'TRUE'=>1, 'UNION'=>1, 'UNIQUE'=>1, 'USER'=>1, 'USING'=>1,
		'WHEN'=>1, 'WHERE'=>1, 'AUTHORIZATION'=>1, 'BETWEEN'=>1, 'BINARY'=>1, 'CROSS'=>1,
		'FREEZE'=>1, 'FULL'=>1, 'ILIKE'=>1, 'INNER'=>1, 'IS'=>1, 'ISNULL'=>1, 'JOIN'=>1,
		'LEFT'=>1, 'LIKE'=>1, 'NATURAL'=>1, 'NOTNULL'=>1, 'OUTER'=>1, 'OVERLAPS'=>1,
		'RIGHT'=>1, 'SIMILAR'=>1, 'VERBOSE'=>1);	

    public function __construct() {
        parent::__construct();

		$this->user = 'postgres';
		$this->port = 5432;
		$this->queryClass = 'FW\DB\PgSQLQuery';
		$this->level = 0;
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
		if ($this->level <= 0) {
			$this->execute("START TRANSACTION");
			$this->level = 1;
		} else {
			$this->level++;
			$this->execute("SAVEPOINT L$this->level");
		}
	}
	
	function commit() {
		if ($this->level <= 0) throw new EDB("Invalid depth of transactions");
		if ($this->level > 1) $this->execute("RELEASE SAVEPOINT L$this->level");
		else $this->execute("COMMIT");
		$this->level--;
	}
	
	function rollback() {
		if ($this->level <= 0) throw new EDB("Invalid depth of transactions");
		if ($this->level > 1) $this->execute("ROLLBACK TO SAVEPOINT L$this->level");
		else $this->execute("ROLLBACK");
		$this->level--;
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
	function seek($row = 0) { pg_result_seek($this->handle, $row); }
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