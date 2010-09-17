<?php
namespace FW\Validate;

class JSON extends Validator {
	
	public $conversion;
	private $validator;
	
	function __construct($validator) {
		$this->validator = $validator;
	}
	
	function validate($value) {
		if (null === ($this->conversion = json_decode($value)))
			throw EValidate('JSON.value');
		if ($this->validator)  $this->validator->validate($this->conversion);
	}
}
