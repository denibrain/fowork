<?php
namespace FW\Validate;

class IP4 extends Validator {
	const ANY = 0;
	const REAL = 1;

	private $type;
	private static $masks = array(0xFF000000, 0xFF000000, 0xFF000000, 0xFF000000, 0xFFFF0000,
					0xFFF00000, 0xFFFFFF00, 0xFFFFFF00, 0xFFFF0000, 0xFFFFFF00,
					0xFFFE0000, 0xFFFFFF00, 0xFFFFFF00, 0xFFFFFFFF);
	private static $no =    array(0x00000000, 0x0A000000, 0x0E000000, 0x7F000000, 0xA9FE0000,
					0xAC100000, 0xC0000200, 0xC0586300, 0xC0A80000, 0xC0000000,
					0xC6120000, 0xC6336400, 0xCB007100, 0xFFFFFFFF);

	function __construct($type = IP4::ANY) {
		$this->type = $type;
	}

	function validate($value) {
		if (false===($ip = ip2long($value))) 
			throw new EValidate('IP4.value');
		if ($this->type == IP4::REAL) {
			foreach(IP4::$masks as $k => $mask) 
				if (($mask & $ip) === IP4::$no[$k])
						throw new EValidate('IP4.real');
		}
	}
}

