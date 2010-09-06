<?php
namespace FW\VCL\Forms;

class Float extends Field {
	public function __construct($name, $caption = '', $required = Field::REQUIRED, $comment = '', $value = '') {
		parent::__construct($name, $caption, $required, $comment, (float)$value);

		$this->filter = array($this, 'onFilter');
		$this->validator = new \FW\Validate\Mask(\FW\Validate\Mask::FLOAT);
	}

	function onFilter($value) {
		return str_replace(array(' ', ','), array('', '.'), $value);
	}

}
