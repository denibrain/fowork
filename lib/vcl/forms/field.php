<?php
namespace FW\VCL\Forms;

/* @property string $name [R] Name of field
 * @property string $value [RW] value of field;
 */
class Field extends FormElement {
	const REQUIRED = 0x01;
	const OPTIONAL = 0x00;
	
	private $value = false;
	private $comment;
	private $require;
	private $caption;

	public $validator;
	public $filter;

	public function __construct($name) {
		parent::__construct($name);
		$this->family = 'field';
		$this->require = Field::REQUIRED;
	}

	/* check value, if error exists then throw exception */
	public function validate($newValue) {
		if ($newValue === '' && $this->require == Field::REQUIRED)
			throw new EFormData('FF.require', $this->name);
		if (isset($this->validator) && $newValue !== '')
			try {
				$this->validator->validate($newValue);
			} catch (\FW\Validate\EValidate $e) {
				throw new EFormData($e->code, $this->name);
			}
	}

	function display() {
		$skeleton = parent::display();
		$skeleton->add(D($this, array('type', 'require,caption,value,comment')));
		return $skeleton;
	}
	
	protected function getName() { return $this->name; } 
	protected function getValue() { return $this->value; }
	protected function setValue($value) {
		if (isset($this->filter))
			$value = call_user_func($this->filter, $value);
		$this->value = $value;
	}
}