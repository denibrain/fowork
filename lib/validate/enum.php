<?php
namespace FW\Validate;

class Enum extends Validator {
	const MULTI = '/(\s|\r|\n)*[,;](\s|\r|\n)*/';
	const MULTIEX = '/(?:\s|\r|\n)+|(?:\s|\r|\n)*[,;](?:\s|\r|\n)*/';
	const SPACE = '/\s+/';
	const NEWLINE = '/\r?\n/';

	private $itemValidator;
	public  $maxCount = 0;
	public  $minCount = 0;

	function __construct($itemValidator, $separator = Enum::MULTI) {
		$this->separator = $separator;
		$this->itemValidator = $itemValidator;
	}

	function validate($value) {
		$a = preg_split($this->separator, $value);
		if ($this->maxCount && $this->maxCount < count($a))
			throw new EValidate('Enum.toomuch');
		if ($this->minCount && $this->minCount > count($a))
			throw new EValidate('Enum.toofew');
		foreach($a as $name)
			$this->itemValidator->validate($name);
	}
}
