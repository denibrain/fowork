<?php
namespace FW\Validate;

class INN extends Validator {

	static $Mask;
	private $reqlen = 10;

	function __construct($len = 10) {
		if ($len!=10 && $len!=12) 
			throw new EValidate("������ ��� ����� ���� ������ 10 ��� 12. ������� 0 ���� ���� ������������ ��� ����");
		$this->reqlen = $len;
	}

	public function validate($value) {
		$l = strlen($value);
		if ($this->reqlen && $l != $this->reqlen)
			throw new EValidate("����� ��� ������ ���� $this->reqlen ����");
		if ($l==10)
			$this->checkINN10($value);
		else
			$this->checkINN12($value);
	}

	private function checkINN10($inn) {
		$koef = array(2,4,10,3,5,9,4,6,8,0);
		if (strlen($inn) != 10) throw new EValidate("����� ��� ������ ���� 10 ����");
		$sum = 0;
		for($i=0; $i<10; ++$i) $sum += $koef[$i] * $inn[$i];
		$no = $sum % 11;
		if ($no == 10) $no = $sum % 10;
		if ($no!=$inn[9]) 
			throw new EValidate("��������� ��� �� ����������", 2);
	}

	private function checkINN12($inn) {
		if (strlen($inn) != 12) throw new EValidate("����� ��� ������ ���� 12 ����");
		$koef = array(7,2,4,10,3,5,9,4,6,8,0);
		$sum = 0;
		for($i=0; $i<11; ++$i) $sum += $koef[$i] * $inn[$i];
		$no = $sum % 11;
		if ($no == 10) $no = $sum % 10;
		if ($no!=$inn[10]) 
			throw new EValidate("��������� ��� �� ����������");
		
	
		$koef = array (3,7,2,4,10,3,5,9,4,6,8,0);
		$sum = 0;
		for($i=0; $i<12; ++$i) $sum += $koef[$i] * $inn[$i];
		$no = $sum % 11;
		if ($no == 10) $no = $sum % 10;
		if ($no!=$inn[11]) 
			throw new EValidate("��������� ��� �� ����������");
	}
}

INN::$Mask = new Mask('/^[0-9]+$/');
?>