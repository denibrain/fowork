<?php
namespace FW\Validate;

class EValidate extends \Exception {

	public $code; // @todo setter

	function __construct($code, $message = '') {
		$this->code = $code;
		parent::__construct("($code) $message");
	}
}; 