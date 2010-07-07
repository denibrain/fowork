<?php
namespace FW\VCL\Forms;

class Checkbox extends FormField {

	public function __construct($name, $caption, $req = FormField::REQUIRED,
								$comment = '', $defValue = 0) {
		parent::__construct($name, $caption, $req, $comment, (int)(!!$defValue));
	}
	
	function validate($value) {}
	
	function setValue($value) { parent::setValue((int)!!$value); }
}

