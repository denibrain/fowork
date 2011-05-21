<?php
namespace FW\VCL\Forms;

class EFormData extends \Exception {
	
	private $codeName;
	private $field;
	
	function __construct($code, $field, $message = '') {
		parent::__construct($message ? $message : $code);
		$this->codeName = $code;
		$this->field = $field;
	}
	
	function __get($key) {
		if ($key == 'code') return $this->codeName;
		elseif ($key == 'field') return $this->field;
	}
}
