<?php
namespace FW\Validate;

class ValidateStack extends Validator {
	const OOR = 1;
	const OAND = 0;
	private $type = 0;
	private $stack = array();

	function __construct() {
		$name = '\FW\Validate\Validator';
		foreach(func_get_args() as $k=>$a) {
			if (is_int($a)) {
				$this->type = $a;
			}
			elseif (is_string($a)) {
				$this->stack[] = new Constant($a);
			}
			elseif (!($a instanceof $name))
				throw new EValidate('Validator.system', "INvalid argument $k");
			else
				$this->stack[] = $a;
		}
	}

	public function add(\FW\Validate\Validator $validator) {
		$this->stack[] = $validator;
	}

	public function validate($value) {
		if ($this->type === ValidateStack::OAND)
			foreach($this->stack as $v) $v->validate($value);
		else {
			$ok = false;
			foreach($this->stack as $v)
				try {
					$v->validate($value);
					$ok = true;
					break;
				} catch (\Exception $e){}
			if (!$ok) {
				throw new EValidate('ValidateStack.nomatch', 'Match not found');
			}
		}
	}
}
?>