<?php
namespace FW\VCL\Forms;

class FormField extends FormElement {
	const REQUIRED = 0x01;
	const OPTIONAL = 0x00;
	
	private $value = false;
	private $name;
	private $comment;
	private $require;
	private $caption;
	private $type;

	public $validator;

	public function __construct($name, $caption, $type = FormField::REQUIRED,
								$comment = '', $defValue = '') {
		$this->name = $name;
		$this->caption = $caption;
		$this->comment = $comment;
		$this->value = $defValue;
		$this->type = strtolower(substr(get_class($this), 9));
		$this->require = $type;
	}

	/* check value, if error exists then throw exception */
	public function validate($newValue) {
		if ($newValue === '' && $this->require == FormField::REQUIRED)
			throw new EFormData('FF.require', $this->name);
		if (isset($this->validator)) $this->validator->validate($newValue);
	}

	function __get($key) {
		switch ($key) {
			case 'name': return $this->name;
			case 'value': return $this->value;
			default:
				return parent::__get($key);
		}
	}
	
	function __set($key, $value) {
		switch ($key) {
			case 'value' :
				$this->value = $value;
				break;
			default:
				parent::__set($key, $value);
		}
	}
	
	function display() {
		return E('field', A(
			'type', $this->type,
			'require', $this->require,
			'name', $this->name,
			'caption', $this->caption,
			'value', $this->value,
			'comment', $this->comment));
	}
}
