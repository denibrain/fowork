<?php
namespace FW\VCL\Forms;

class Text extends FormField {
	
	private $maxlen = false;
	private $minlen = false;

	function __set($key, $value) {
		switch($key) {
			case 'maxlen': $this->maxlen = (int) $value; break;
			case 'minlen': $this->minlen = (int) $value; break;
			default:
				parent::__set($key, $value);
		}
	}
	
	function validate($value) {
		parent::validate($value);
		if (false!==$this->maxlen && strlen($value) > $this->maxlen)
			throw new EFormData('FFTEXT.maxlen', $this->name);
		if (false!==$this->minlen && strlen($value)	< $this->minlen)
			throw new EFormData('FFTEXT.minlen', $this->name);
	}
	
	function display() {
		$e = parent::display();
		$e->add(A('maxlen', $this->maxlen, 'minlen', $this->minlen));
		return $e;
	}
}
