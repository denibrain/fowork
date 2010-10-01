<?php
namespace FW\Validate;

class BankAccount extends Validator {
	const SETTLEMENT = 0;
	const CORRESPONDENT = 1;

	private $type;
	static $Mask;
	public $onBIK;

	function __construct($type) {
		$this->type = $type;
	}

	function calcCS($value) {
		// 2. ����������� ����������� ����� �� ���������� �������� 
		//	  ��������������: (7,1,3,7,1,3,7,1,3,7,1,3,7,1,3,7,1,3,7,1,3,7,1)
		$a = array(7, 1, 3);
		$total = 0;
		$value = str_split($value);
		foreach($value as $key=>$item) {
			$total = $total + $item * $a[$key % 3];
		}
		// 3. ����������� ����������� ����� ��� ������� �� ������� 
		//    ����������� ����� �� 10
		// 4. ����������� ����� ������������ � ����. � ������ �� 
		//    ��������� ���������� ���� ��������� ����������.
		return $total % 10 == 0;
	}

	function checkSettAccount($value, $bik) {
		// 1. ��� �������� ����������� ����� ����� ��������� ������ 
		//    ����������� ��� ��������� ����� ���� �����.
		if (!$this->calcCS(substr($bik, -3).$value))
			throw new EValidate('Accounting.settacc');
	}
	
	function checkCorrAccount($value, $bik) {
		// 1. ��� �������� ����������� ����� ����� ����������������� ������ 
		// ����������� "0" � ��� ����� ���� �����, ������� � ������ �����.

		if (!$this->calcCS("0".substr($bik, 4, 2).$value))
			throw new EValidate('Accounting.corracc');
	}

	function validate($value) {
		BankAccount::$Mask->validate($value);
		if (isset($this->onBIK)) {
			$bik = call_user_func($this->onBIK);
			if ($this->type === BankAccount::SETTLEMENT)
				$this->checkSettAccount($value, $bik);
			else
				$this->checkCorrAccount($value, $bik);
			
		}
	}
}

BankAccount::$Mask = new Mask("/^[0-9]{20}$/");