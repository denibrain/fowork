<?php
require_once 'PHPUnit/Framework.php';
require_once "lib/fwork.php";

use FW\Text\Cases as Cases;

class CasesTest extends PHPUnit_Framework_TestCase {

	function testFormat() {
		$cases = new Cases();
		$this->assertEquals("������ ���� ����������", 
			$cases->format("������ ���� ����������", Cases::NOMINATIVE));
		$this->assertEquals("������� ����� �����������", 
			$cases->format("������ ���� ����������"));
		$this->assertEquals("������� ����� �����������", 
			$cases->format("������� ���� ����������"));
		$this->assertEquals("������� ��������� �����������", 
			$cases->format("������ �������� ����������"));
		$this->assertEquals("������ ������� �����������", 
			$cases->format("����� ������ ����������"));
		$this->assertEquals("���� ������� �����������", 
			$cases->format("���� ������ ����������"));
		$this->assertEquals("������� ������� �����������", 
			$cases->format("������� ������ ����������"));
		$this->assertEquals("������� ������� �����������", 
			$cases->format("������� ������ ����������"));
		$this->assertEquals("����� ������� �����������", 
			$cases->format("����� ������ ����������"));
		$this->assertEquals("��������� ������� �����������", 
			$cases->format("�������� ������ ����������"));
		$this->assertEquals("������� ������� �����������", 
			$cases->format("������ ������ ����������"));
		$this->assertEquals("������������ ���������", 
			$cases->format2("����������� ��������"));

	}
}

?>