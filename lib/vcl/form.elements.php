<?php
namespace FW\VCL;
class FFComoboBox extends FFList {}
class FFDate extends FormField {}
class FFTime extends FormField {}
class FFInt extends FormField {
	function validate($value) {
		parent::validate($value);

		if (!preg_match('/^-?[0-9]+$/', $value))
			throw new EFormData('FFINT.NaN', $this->name);
	}	
}

class FFFloat extends FormField {}
class FFCurrency extends FFFloat {}
class FFFile extends FFList {

	private $minsize = false;
	private $maxsize = false;
	private $mimes = array();
	
	function validate($value) {
		
	}
}
?>