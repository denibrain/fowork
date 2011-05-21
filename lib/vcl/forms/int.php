<?php
namespace FW\VCL\Forms;

class Int extends Field {
	function validate($value) {
		parent::validate($value);

		if (!preg_match('/^-?[0-9]+$/', $value))
			throw new EFormData('FFINT.NaN', $this->name);
	}	
}
