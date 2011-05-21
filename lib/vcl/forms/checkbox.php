<?php
namespace FW\VCL\Forms;

class Checkbox extends Field {

	public function __construct($name, $caption, $req = Field::REQUIRED,
								$comment = '', $defValue = 0) {
		parent::__construct($name, $caption, $req, $comment, (int)(!!$defValue));
	}
	
	function validate($value) {}
	
	function setValue($value) { parent::setValue((int)!!$value); }
}

