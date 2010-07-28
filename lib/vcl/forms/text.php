<?php
namespace FW\VCL\Forms;
use \FW\Validate\Mask;

class Text extends Field {
	protected function setValue($value) { parent::setValue(trim($value)); }

	function validate($value) {
		if (!isset($this->validator)) 
			$this->validator = new Mask(Mask::TEXTLINE);
		parent::validate($value);
	}
}