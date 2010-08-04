<?php
namespace FW\VCL\Forms;

class Button extends \FW\VCL\Component {

	const CUSTOM = 'button';
	const SUBMIT = 'submit';
	const RESET = 'reset';
	const CANCEL = 'cancel';
	
	private $caption;
	protected $type;
	
	function __construct($name, $caption = '', $type = Button::SUBMIT) {
		parent::__construct($name);
		$this->family = 'button';
		$this->caption = $caption;
		$this->type = $type;
	}
	
	function display() {
		$skeleton = parent::display();
		$skeleton->add(D($this, 'caption,type'));
		return $skeleton;
	}

	function getType() {return $this->type;}
	function getCaption() {return $this->caption;}
}

