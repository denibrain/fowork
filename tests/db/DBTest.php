<?php

require_once 'PHPUnit/Framework.php';
require_once "lib/fwork.php";
require_once "testapp/etc/app.cfg.php";


class DBTest extends PHPUnit_Framework_TestCase {

	function testPg() {
		$db = \FW\DB\DB::connect("pgsql://postgres:admin@localhost/testwork");

		$db("DELETE FROM author;");
		$db("INSERT INTO author (name) VALUES ('Ahmatova')");
		$q = $db("SELECT * FROM author ");
		$this->assertEquals("1", $q->count());

		$db("DELETE FROM author;");
		$db->begin();
		$db("INSERT INTO author (name) VALUES ('Ahmatova')");
		$db("INSERT INTO author (name) VALUES ('Tolstoy')");
		$db("INSERT INTO author (name) VALUES ('Pushkin')");
		$db("INSERT INTO author (name) VALUES ('Turgenev')");
		$db->rollback();

		$q = $db("SELECT * FROM author ");
		
		$this->assertEquals("0", $q->count());

		$db->begin();
		$db("INSERT INTO author (name) VALUES ('Ahmatova')");
			$db->begin();
			$db("INSERT INTO author (name) VALUES ('Tolstoy')");
			$db->commit();
		$db("INSERT INTO author (name) VALUES ('Pushkin')");
		$db("INSERT INTO author (name) VALUES ('Turgenev')");
		$db->rollback();

		$q = $db("SELECT * FROM author ");
		
		$this->assertEquals("0", $q->count());

	}

}

?>