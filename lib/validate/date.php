<?php
namespace FW\Validate;

class Date extends Mask {

	private $dayIndex = false;
	private $monthINdex = false;
	private $yearMonth = false;

	function __construct($format = "d.m.Y") {
		$g = 0;
		$self = $this;
		$mask = preg_replace_callback("[dmY]", function($matches) use ($self, $g) {
			$m = $matches[0];
			if ($m === 'd') {
				if ($self->dayIndex !== false) 
					throw new EValidate('Validate.system', 'Day mask define already');
				$this->dayIndex = ++$g;
				return "([0-9]{2})";
			}
			if ($m === 'm') {
				if ($self->monthIndex !== false) 
					throw new EValidate('Validate.system', 'Day mask define already');
				$this->monthIndex = ++$g;
				return "([0-9]{2})";
			}
			if ($m === 'Y') {
				if ($self->yearIndex !== false) 
					throw new EValidate('Validate.system', 'Day mask define already');
				$this->yearIndex = ++$g;
				return "([0-9]{4})";
			}
		}, preg_quote($format, '/'));
		parent::__construct($mask);
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