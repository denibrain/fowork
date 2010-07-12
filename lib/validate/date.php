<?php
namespace FW\Validate;

class Date extends Mask {

	private $dayIndex = false;
	private $monthIndex = false;
	private $yearIndex = false;

	function __construct($format = "d.m.Y") {
		$g = 0;
		$di = $mi = $yi = false;
		echo $mask = preg_replace_callback("/[dmY]/", function($matches) use ($di, $mi, $yi, $g) {
			$m = $matches[0];
			if ($m === 'd') {
				if ($di !== false) 
					throw new EValidate('Validate.system', 'Day mask define already');
				$di = ++$g;
				return "([0-9]{2})";
			}
			if ($m === 'm') {
				if ($mi !== false) 
					throw new EValidate('Validate.system', 'Day mask define already');
				$mi = ++$g;
				return "([0-9]{2})";
			}
			if ($m === 'Y') {
				if ($yi !== false) 
					throw new EValidate('Validate.system', 'Day mask define already');
				$yi = ++$g;
				return "([0-9]{4})";
			}
		}, preg_quote($format, '/'));
		
		$this->dayIndex = $di;
		$this->monthIndex = $mi;
		$this->yearIndex = $yi;
		parent::__construct("/^$mask\$/");
	}

	function __validate($value) {
		parent::validate($value);
                if (!checkdate(
			$this->matches[$this->monthIndex],
			$this->matches[$this->dayIndex],
			$this->matches[$this->yearIndex]
		)) throw new EValidate('Date.value');
	}
}