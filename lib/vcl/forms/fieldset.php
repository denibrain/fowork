<?php
namespace FW\VCL\Forms;

class FieldSet extends \FW\VCL\Component  {
	
	private $caption;
	
	function __construct($name, $caption  = '') {
		parent::__construct($name);
		$this->caption = $caption;
		$this->family = 'fieldset';
	}

	function add($control) {
		if ($control instanceof \FW\VCL\Forms\Field) $this->owner->fields->add($control);
		elseif ($control instanceof \FW\VCL\Forms\Button) $this->owner->buttons->add($control);
		return parent::add($control);
	}
	
	function customDisplay($skeleton) {
		$skeleton->add(A('caption', $this->caption));
	}
}