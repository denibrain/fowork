<?php
require_once 'PHPUnit/Framework.php';
require_once "lib/fwork.php";

use FW\Util\DateTime as DateTime;

class DateTimeTest extends PHPUnit_Framework_TestCase {

	function testCopy() {
		$original = new DateTime(time());
		$copy = new DateTime($original);
		$this->assertEquals($original->date, $copy->date);
	}

	/**
	 * @dataProvider dataValidString
	 */
	function testCreateFromValidString($s, $d) {
		$dt = new DateTime($s);
		$this->assertEquals($dt->day, $d);
	}

	function dataValidString() {
		return array(
			array("2004-03-12", 12),
			array("2004-03-29 23:12", 29),
			array("2004-03-08 23:23:23", 8),
			array("2004-02-29", 29)
		);
	}

	function testInterval() {
		$dt = new DateTime('2005-07-12');
		$dt2 = new DateTime('2006-05-12');
		$this->assertEquals(1, $dt2->interval($dt, DateTime::IYEAR));
		$this->assertEquals(10, $dt2->interval($dt, DateTime::IMONTH));
		$this->assertEquals(304, $dt2->interval($dt, DateTime::IDAY));
	}
	
	function testAddInterval() {
		$dt = new DateTime('2005-07-31');
		
		$dt->addInterval(1, DateTime::IMONTH);
		$this->assertEquals(31, $dt->day);
		$dt->addInterval(6, DateTime::IMONTH);
		$this->assertEquals(28, $dt->day);
		$dt->addInterval(2, DateTime::IYEAR);
		$this->assertEquals(28, $dt->day);
		$dt->addInterval(1, DateTime::IDAY);
		$this->assertEquals(29, $dt->day);
		$this->assertEquals("2008-02-29", $dt->date);
		$dt->addInterval(365, DateTime::IDAY);
		$this->assertEquals(1, $dt->day);
	}
}

?>