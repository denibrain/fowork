<?php
namespace FW\VCL\Forms;
use \FW\Validate\Mask;

class Memo extends Text {
	function validate($value) {
		if (!isset($this->validator)) 
			$this->validator = new Mask(Mask::TEXT);

		parent::validate($value);
	}
}