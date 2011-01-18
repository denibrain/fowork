<?php
namespace FW\Validate;

/* 
 * @property int $max [RW] maximum input length
 * @property int $min [RW] minimu length
 */
class Length extends Validator  {

	private $max = false;
	private $min = false;

	function __construct($max, $min = false) {
		$this->max = $max;
		$this->min = $min;
	}

	function validate($value) {
		if (false!==$this->max && strlen($value) > $this->max)
			throw new EValidate('Length.max', strlen($value)." > $this->max");
		if (false!==$this->min && strlen($value) < $this->min)
			throw new EValidate('Length.min', strlen($value)." < $this->min");
	}

	protected function setMax($value) { if ($value < 0) $this->max = false; $this->max = (int) $value; }
	protected function setMin($value) { if ($value < 0) $this->min = false; $this->min = (int) $value; }
	protected function getMax() { return $this->max; }
	protected function getMin() { return $this->min; }

}