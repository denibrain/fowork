<?php
namespace FW\VCL;

class FFMemo extends FFText {}

class FFHidden extends FFText {}

class FFCheckbox extends FormField {

	public function __construct($name, $caption, $req = FormField::REQUIRED,
								$comment = '', $defValue = 0) {
		parent::__construct($name, $caption, $req, $comment, (int)(!!$defValue));
	}
	
	function validate($value) {}
	
	function __set($key, $value) {
		switch ($key) {
			case 'value' :parent::__set($key, (int)!!$value);
				break;
			default:
				parent::__set($key, $value);
		}
	}
	
}

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