<?php
require_once 'PHPUnit/Framework.php';
require_once "lib/fwork.php";

class FileTest extends PHPUnit_Framework_TestCase {

	function testWrire() {
		F("demo.txt")->write("foo");
	}

	function testRead() {
		$this->assertEquals("foo", F("demo.txt")->read());
	}

	function testDelete() {
		F("demo.txt")->delete();
	}

}

?>