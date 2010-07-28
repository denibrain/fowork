<?php
namespace FW\VCL\Forms;

class Button extends \FW\VCL\Component {

	const CUSTOM = 'button';
	const SUBMIT = 'submit';
	const RESET = 'reset';
	const CANCEL = 'cancel';
	
	private $caption;
	private $name;
	protected $type;
	
	function __construct($name) {
		$this->family = 'button';
		$this->type = Button::SUBMIT;
	}
	
	function display() {
		return E('button', A(
			'type', $this->type,
			'caption', $this->caption,
			'name', $this->name
		));
	}
	
	function __get($key) {
		switch ($key) {
			case 'name': return $this->name;
			case 'type': return $this->type;
			default:
				return parent::__get($key);
		}
	}
}

