<?php
namespace FW\Validate;

class Constant extends Validator {
	
	private $value;
	
	function __construct($value) {
		$this->value = $value;
	}
	
	function validate($value) {
		if ($this->value !== $value) {
			throw new EValidate('Constant.value');
		}
	}
}