<?php
namespace FW\Validate;

class Mask extends Validator {

	const TEXTLINE = "/^[\\\\\\/()\"!+:;'�,.0-9A-Za-z\x7F-\xFF\\n\\r -]+$/";
	const TEXT = "/^[\\n\\r\\t*\\/()\"!:;'�,.0-9A-Za-z�-��-� -]+$/s";
	const LATIN = "/^[a-zA-Z]+$/";
	const LATINTEXT = "/^[a-zA-Z-\\s]+$/";
	const NUMLATIN ="/^[0-9a-zA-Z_-]+$/";
	const LETTERS = "/^[a-z�-��-߸�]+$/i";
	const LETTERSEX = "/^[a-z�-��-߸�-]+$/i";
	const LOGIN = '/^[a-zA-Z0-9_.-]+$/';
	const PASSWORD = '/[a-zA-Z0-9!@#$%^&*()\[\]|+=\/\\\\_-]+/';
	const INT = '/^\d+$/';
	const FLOAT = '/^\d+(?:\.\d+)?$/';
	const CURRENCY = '/^\d+(?:\.\d{1,2})?$/';

	private $mask;
	public $matches;

	function __construct($mask = Mask::TEXT) {
		$this->mask = $mask;
	}

	function validate($value) {
		if (!preg_match($this->mask, $value, $this->matches)) 
			throw new EValidate("Mask.mask");
	}

}