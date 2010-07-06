<?php
namespace FW\VCL\Forms;

class Password extends Text {
	public $primary = false;
	
	function validate($value) {
		parent::validate($value);
		
		if (false!==$this->primary && $this->primary->value != $value)
			throw new EFormData('FFPASSWORD.noteq', $this->name);
	}
}
