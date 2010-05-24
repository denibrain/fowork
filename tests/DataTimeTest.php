<?php
require_once 'PHPUnit/Framework.php';
require_once dirname(dirname(__FILE__))."/lib/fwork.php";

class DateTimeTest extends PHPUnit_Framework_TestCase {

	/**
	 * @dataProvider dataValidString
	 */
	function testCreateFromValidString($s, $d) {
		$dt = new \FW\Util\DateTime($s);
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
		$dt = new \FW\Util\DateTime('2005-07-12');
		$dt2 = new \FW\Util\DateTime('2006-05-12');
		$this->assertEquals(1, $dt2->interval($dt, DTI_YEAR));
		$this->assertEquals(10, $dt2->interval($dt, DTI_MONTH));
		$this->assertEquals(304, $dt2->interval($dt, DTI_DAY));
	}
	
	function testAddInterval() {
		$dt = new \FW\Util\DateTime('2005-07-31');
		
		$dt->addInterval(1, DTI_MONTH);
		$this->assertEquals(31, $dt->day);
		$dt->addInterval(6, DTI_MONTH);
		$this->assertEquals(28, $dt->day);
		$dt->addInterval(2, DTI_YEAR);
		$this->assertEquals(28, $dt->day);
		$dt->addInterval(1, DTI_DAY);
		$this->assertEquals(29, $dt->day);
		$this->assertEquals("2008-02-29", $dt->date);
		$dt->addInterval(365, DTI_DAY);
		$this->assertEquals(1, $dt->day);
	}
}

?>
