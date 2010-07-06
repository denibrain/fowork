<?php
namespace FW\Validate;

class EValidate extends \Exception {

	public $code; // @todo setter

	function __consttruct($code) {
		$this->code = $code;
		parent::__construct($code);
	}
}; 