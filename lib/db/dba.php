<?php
namespace FW;

class DBAdmin extends Object {
	private $db;

	function __construct() {
		$this->db = DB::connect();
	}

	function fromDB() {
		$path = FW_PTH_DB.'tables/';
		$q=$this->db->execf("SHOW TABLES");
		$p = $this->db->prefix;
		$relations = array();
		while(list($tableName) = $q->get()) {
			$relations[$tableName] = array();
			$table = array();
			foreach($this->db->execf("SHOW COLUMNS FROM `$tableName`") as $field) {
				$f = $field['Field'];
				$table[$f] = $field;
				if (preg_match('/^(.*)_id$/', $f, $matches) && 'parent_id' != $f) {
					$relations[$tableName][$matches[1]] = "`$tableName`.`$f` = `$p$matches[1]`.id";
				}
			}
			file_put_contents(FW_PTH_DB."tables/$tableName.php", var_export($table, true));
		}
		file_put_contents(FW_PTH_DB."relations.php", "<?php\n\$this->relations = ".var_export($relations, true)."\n?>");
		
	}

	function toDB() {
	}
}

?>