<?php
namespace FW\Validate;

class ArrayValidator extends Validator {
	
	private $validator = null;
	
	function __construct($validator) {
		$this->validator = $validator;
	}
	
	function validate($value) {
		if (!is_array($value)) 
			throw new EValidate('ArrayValidator.dataType');
		foreach($value as $item)
			$this->validator->validate($item);
	}
}