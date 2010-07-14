<?php
namespace FW\Validate;

class Record extends Validator {
	const MULTI = '/(\s|\r|\n)*[,;](\s|\r|\n)*/';
	const MULTIEX = '/(?:\s|\r|\n)+|(?:\s|\r|\n)*[,;](?:\s|\r|\n)*/';
	const SPACE = '/\s+/';
	const NEWLINE = '/\r?\n/';

	private $validators;
	private $separator;
	private $size;
	private $minSize = false;

	function __construct(/* mixed Validators/Separator */) {
		$name = '\FW\Validate\Validator';
		foreach(func_get_args() as $k=>$a) {
			if (is_string($a)) {
				$this->separator = $a;
			}
			elseif (is_int($a)) {
				$this->minSize = $a;
			}
			elseif (!($a instanceof $name))
				throw new EValidate('Validator.system', 'Invalid argument $k');
			else
				$this->validators[] = $a;
		}
		$this->size = count($this->validators);
		if (false=== $this->minSize) $this->minSize = $this->size;
	}

	function validate($value) {
		$a = preg_split($this->separator, $value);
		$c = count($a);
		
		if ($c > $this->size)
			throw new EValidate('Record.extrafield');
		if ($c < $this->minSize)
			throw new EValidate('Record.fewfield');
			
		foreach($a as $k=>$name)
			$this->validators[$k]->validate($name);
	}
}
