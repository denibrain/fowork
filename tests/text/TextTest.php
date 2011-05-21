<?php
require_once 'PHPUnit/Framework.php';
require_once "lib/fwork.php";

use FW\Text\Text as Text;

class TextTest extends PHPUnit_Framework_TestCase {

	function testSetEOL() {
		$this->assertEquals("\r\n\r\n", T("\r\n\n")->setEOL("\r\n"));
	}

}

?>