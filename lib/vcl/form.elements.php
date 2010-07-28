<?php
namespace FW\VCL;
class FFComoboBox extends FFList {}
class FFDate extends Field {}
class FFTime extends Field {}
class FFInt extends Field {
	function validate($value) {
		parent::validate($value);

		if (!preg_match('/^-?[0-9]+$/', $value))
			throw new EFormData('FFINT.NaN', $this->name);
	}	
}

class Float extends Field {
	function __construct($name, $owner) {
		parent::__construct($owner);
		$this->filter = function ($value) {
			return $value(str_replace(array(' ', ','), array('', '.'), $subject));
		};
		$this->validator = new \FW\Validate\Mask(\FW\Validate\Mask::FLOAT);
	}
}

class FFFile extends FFList {

	private $minsize = false;
	private $maxsize = false;
	private $mimes = array();
	
	function validate($value) {
		
	}
}
?>