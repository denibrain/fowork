<?php
require_once 'PHPUnit/Framework.php';
require_once "lib/fwork.php";

use FW\Text\Cases as Cases;

class CasesTest extends PHPUnit_Framework_TestCase {

	function testFormat() {
		$cases = new Cases();
		$this->assertEquals("Иванов Петр Викторович", 
			$cases->format("Иванов Петр Викторович", Cases::NOMINATIVE));
		$this->assertEquals("Иванова Петра Викторовича", 
			$cases->format("Иванов Петр Викторович"));
		$this->assertEquals("Русских Бенца Викторовича", 
			$cases->format("Русских Бенц Викторович"));
		$this->assertEquals("Сакаева Искандера Викторовича", 
			$cases->format("Сакаев Искандер Викторович"));
		$this->assertEquals("Хазина Михаила Леонидовича", 
			$cases->format("Хазин Михаил Леонидович"));
		$this->assertEquals("Осла Михаила Леонидовича", 
			$cases->format("Осел Михаил Леонидович"));
		$this->assertEquals("Соловья Михаила Леонидовича", 
			$cases->format("Соловей Михаил Леонидович"));
		$this->assertEquals("Воробья Михаила Леонидовича", 
			$cases->format("Воробей Михаил Леонидович"));
		$this->assertEquals("Немца Михаила Леонидовича", 
			$cases->format("Немец Михаил Леонидович"));
		$this->assertEquals("Кормильца Михаила Леонидовича", 
			$cases->format("Кормилец Михаил Леонидович"));
		$this->assertEquals("Силийца Михаила Леонидовича", 
			$cases->format("Силиец Михаил Леонидович"));
		$this->assertEquals("генерального директора", 
			$cases->format2("генеральный директор"));

	}
}

?>