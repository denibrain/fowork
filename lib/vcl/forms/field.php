<?php
namespace FW\VCL\Forms;

/* @property string $name [R] Name of field
 * @property string $value [RW] value of field;
 */
class Field extends \FW\VCL\Component {
	const REQUIRED = 0x01;
	const OPTIONAL = 0x00;
	
	private $value = false;
	private $comment;
	private $require;
	private $caption;

	public $validator = null;
	public $filter;

	public function __construct($name, $caption = '', $required = Field::REQUIRED, $comment = '', $value = '') {
		parent::__construct($name);
		$this->family = 'field';
		$this->caption = $caption;
		$this->require = $required;
		$this->comment = $comment;
		$this->value = $value;
	}

	public function addValidator(\FW\Validate\Validator $validator) {
		if (isset($this->validator)) {
			$v = new \FW\Validate\ValidateStack($this->validator);
			$v->add($validator);
			$this->validator = $v;
		} else {
			$this->validator = $validator;
		}
		return $this;
	}

	/* check value, if error exists then throw exception */
	public function validate($newValue) {
		if ($newValue === '' && $this->require == Field::REQUIRED)
			throw new EFormData('FF.require', $this->name);
		if (isset($this->validator) && $newValue !== '')
			try {
				$this->validator->validate($newValue);
			} catch (\FW\Validate\EValidate $e) {
				throw new EFormData($e->code, $this->name, $e->getMessage());
			}
	}

	function display() {
		$skeleton = parent::display();
		$skeleton->add(D($this, 'require,caption,value,comment'));
		return $skeleton;
	}

	function getComment() { return $this->comment; }
	function setComment($value) { $this->comment = $value; }
	function getCaption() { return $this->caption; }
	function setCaption($value) { $this->caption = $value; }
	function getRequire() { return $this->require; }
	function getValue() { return $this->value; }
	function setValue($value) {
		if (isset($this->filter))
			$value = call_user_func($this->filter, $value);
		$this->value = $value;
	}
}