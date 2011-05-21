<?php
require_once 'PHPUnit/Framework.php';
require_once "lib/fwork.php";

class DatasetTest extends PHPUnit_Framework_TestCase {

	function testPgWhere() {
		$db = new FW\DB\PgSQL();
		$ds = new FW\DB\Dataset("tableA[id=1]", array(), $db);
		$this->assertEquals("SELECT * FROM \"tableA\" AS t0 WHERE (t0.\"id\" = 1)", $ds->sql);

		$ds = new FW\DB\Dataset("tableA[?id=:id]", array(), $db);
		$this->assertEquals("SELECT * FROM \"tableA\" AS t0", $ds->sql);

		$ds = new FW\DB\Dataset("tableA[?id=:id]", array('id'=>'z'), $db);
		$this->assertEquals("SELECT * FROM \"tableA\" AS t0 WHERE (t0.\"id\" = 'z')", $ds->sql);

		$ds = new FW\DB\Dataset("tableA[?id=':{id}']", array('id'=>'y'), $db);
		$this->assertEquals("SELECT * FROM \"tableA\" AS t0 WHERE (t0.\"id\" = 'y')", $ds->sql);

		$ds = new FW\DB\Dataset("tableA[?id=':{id}'::int]", array('id'=>'x'), $db);
		$this->assertEquals("SELECT * FROM \"tableA\" AS t0 WHERE (t0.\"id\" = 'x'::int)", $ds->sql);

		$ds = new FW\DB\Dataset("tableA [id=2] &[x=1]", array(), $db);
		$this->assertEquals("SELECT * FROM \"tableA\" AS t0 WHERE (t0.\"id\" = 2) HAVING (t0.\"x\" = 1)", $ds->sql);
	}

}

?>