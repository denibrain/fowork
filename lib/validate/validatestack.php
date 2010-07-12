<?php
namespace FW\Validate;

class ValidateStack extends Validator {

	private $stack = array();

	function __construct() {
		$name = '\FW\Validate\Validator';
		foreach(func_get_args() as $k=>$a) {
			if (!($a instanceof $name))
				throw new EValidate('Validator.system', 'INvalid argument $k');
			$this->stack[] = $a;
		}
	}	

	public function validate($value) {
		foreach($this->stack as $v) $v->validate($value);
	}
}
?>