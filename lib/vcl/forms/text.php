<?php
namespace FW\VCL\Forms;
use \FW\Validate\Mask;

/* 
 * @property int $maxlen [RW] maximum input length
 * @property int $minlen [RW] minimu length
 */
class Text extends FormField {
	private $maxlen = false;
	private $minlen = false;

	protected function setMaxlen($value) { $this->maxlen = (int) $value; }
	protected function setMinlen($value) { $this->minlen = (int) $value; }
	protected function getMaxlen() { return $this->maxlen; }
	protected function getMinlen() { return $this->minlen; }
	protected function setValue($value) { parent::setValue(trim($value)); }

	function validate($value) {
		if (!isset($this->validator)) 
			$this->validator = new Mask(Mask::TEXTLINE);
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
