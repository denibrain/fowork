<?php
namespace FW\Validate;

class ObjectValidator extends Validator {
	
	private $validators = array();
	
	function __construct($validators) {
		$name = '\FW\Validate\Validator';
		foreach($validators as $fieldName => $validator) {
			if (0===strpos($fieldName, '?')) {
				$req = 0;
				$fieldName = substr($fieldName, 1);
			} else $req = 1;
			if (is_string($validator)) {
				$this->validators[$fieldName] = array(new Constant($validator), $req);
			}
			elseif (!($validator instanceof $name))
				throw new EValidate('Validator.system', 'INvalid argument $k');
			else
				$this->validators[$fieldName] = array($validator, $req);
		}
	}
	
	function validate($value) {
		foreach($this->validators as $fieldName => $v) {
			list($validator, $req) = $v;
			if (!isset($value->$fieldName)) {
				if ($req) throw new EValidate('ObjectValidator.fieldMissed', $fieldName);
			}
			else
				$validator->validate($value->$fieldName);
		}
	}
}
?>