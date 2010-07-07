<?php
namespace FW\VCL\Forms;

/* @property string $name [R] Name of field
 * @property string $value [RW] value of field;
 */
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
		$this->type = strtolower(substr(get_class($this), 13));
		$this->require = $type;
	}

	/* check value, if error exists then throw exception */
	public function validate($newValue) {
		if ($newValue === '' && $this->require == FormField::REQUIRED)
			throw new EFormData('FF.require', $this->name);
		if (isset($this->validator) && $newValue !== '')
			try {
				$this->validator->validate($newValue);
			} catch (\FW\Validate\EValidate $e) {
				throw new EFormData($e->code, $this->name);
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
	
	protected function getName() { return $this->name; } 
	protected function getValue() { return $this->value; }
	protected function setValue($value) { $this->value = $value; }
}
