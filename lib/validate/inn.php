<?php
namespace FW\Validate;

class INN extends Validator {

	static $Mask;
	private $reqlen = 10;

	function __construct($len = 10) {
		if ($len!=10 && $len!=12) 
			throw new EValidate("Длинна ИНН может быть только 10 или 12. Укажите 0 если надо использовать оба типа");
		$this->reqlen = $len;
	}

	public function validate($value) {
		$l = strlen($value);
		if ($this->reqlen && $l != $this->reqlen)
			throw new EValidate("Длина ИНН должна быть $this->reqlen цифр");
		if ($l==10)
			$this->checkINN10($value);
		else
			$this->checkINN12($value);
	}

	private function checkINN10($inn) {
		$koef = array(2,4,10,3,5,9,4,6,8,0);
		if (strlen($inn) != 10) throw new EValidate("Длина ИНН должна быть 10 цифр");
		$sum = 0;
		for($i=0; $i<10; ++$i) $sum += $koef[$i] * $inn[$i];
		$no = $sum % 11;
		if ($no == 10) $no = $sum % 10;
		if ($no!=$inn[9]) 
			throw new EValidate("Введенный ИНН не существует", 2);
	}

	private function checkINN12($inn) {
		if (strlen($inn) != 12) throw new EValidate("Длина ИНН должна быть 12 цифр");
		$koef = array(7,2,4,10,3,5,9,4,6,8,0);
		$sum = 0;
		for($i=0; $i<11; ++$i) $sum += $koef[$i] * $inn[$i];
		$no = $sum % 11;
		if ($no == 10) $no = $sum % 10;
		if ($no!=$inn[10]) 
			throw new EValidate("Введенный ИНН не существует");
		
	
		$koef = array (3,7,2,4,10,3,5,9,4,6,8,0);
		$sum = 0;
		for($i=0; $i<12; ++$i) $sum += $koef[$i] * $inn[$i];
		$no = $sum % 11;
		if ($no == 10) $no = $sum % 10;
		if ($no!=$inn[11]) 
			throw new EValidate("Введенный ИНН не существует");
	}
}

INN::$Mask = new Mask('/^[0-9]+$/');
?>