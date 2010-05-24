<?php
namespace FW\VCL;

define('FW_FF_NOREQUIRE', 0x00);
define('FW_FF_REQUIRE', 0x01);
define('FW_FS_NONE', 0x00);
define('FW_FS_ERROR', 0x01);
define('FW_FS_OK', 0x02);
define('FW_FS_SUBMITTED', 0x03);

class EFormData extends \Exception {
	
	private $codeName;
	private $field;
	
	function EFormData($code, $field, $message = '') {
		parent::__construct($message ? $message : $code);
		$this->codeName = $code;
		$this->field = $field;
	}
	
	function __get($key) {
		if ($key == 'code') return $this->codeName;
		elseif ($key == 'field') return $this->field;
	}
}

class FormElement extends  \FW\Object {
	public $form;
	public function display() {return E();}
}

class FormButton extends FormElement {
	
	private $caption;
	private $name;
	private $type = 'button';
	
	function __construct($caption, $name = '') {
		$this->caption = $caption;
		$this->name = $name;
	}
	
	function display() {
		return E('button', A(
			'type', $this->type,
			'caption', $this->caption,
			'name', $this->name
		));
	}
}

class FBSubmit extends FormButton {
	function __construct($caption = 'отправить', $name = '') {
		parent::__construct($caption, $name);
	}
}

class FSReset extends FormButton {
	function __construct($caption = 'очистить', $name = '') {
		parent::__construct($caption, $name);
	}
}


class FormField extends FormElement {
	private $value = false;
	private $name;
	private $comment;
	private $require;
	private $caption;
	private $type;

	public function __construct($name, $caption, $req = FW_FF_NOREQUIRE,
								$comment = '', $defValue = '') {
		$this->name = $name;
		$this->caption = $caption;
		$this->comment = $comment;
		$this->value = $defValue;
		$this->type = strtolower(substr(get_class($this), 9));
		$this->require = $req;
	}

	/* check value, if error exists then throw exception */
	public function validate($newValue) {
		if ($newValue === '' && $this->require)
			throw new EFormData('FF.require', $this->name);
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


class FFText extends FormField {
	
	private $mask = false;
	private $maskName = false;
	private $maxlen = false;
	private $minlen = false;

	function __set($key, $value) {
		switch($key) {
			case 'mask': $this->mask = $value; break;
			case 'maskName': $this->maskName = $value; break;
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
		if ((false!==$this->mask || $this->maskName) && $value) {
			if ($this->maskName) {
				try {
					\FW\Util\Validator::validate($value, $this->maskName);
				} catch (\FW\Util\EValidate $e) {
					throw new EFormData('FFTEXT.mask', $this->name, $e->getMessage());
				}
			} elseif (!preg_match($this->mask, $value))
				throw new EFormData('FFTEXT.mask', $this->name);
		}
	}
	
	function display() {
		$e = parent::display();
		$e->add(A('maxlen', $this->maxlen, 'minlen', $this->minlen,
			'mask', $this->mask, 'maskName', $this->maskName));
		return $e;
	}
}

class FFMemo extends FFText {}

class FFHidden extends FFText {}

class FFPassword extends FFText {
	public $primary = false;
	
	function validate($value) {
		parent::validate($value);
		
		if (false!==$this->primary && $this->primary->value != $value)
			throw new EFormData('FFPASSWORD.noteq', $this->name);
	}
}

class FFCheckbox extends FormField {

	public function __construct($name, $caption, $req = FW_FF_NOREQUIRE,
								$comment = '', $defValue = 0) {
		parent::__construct($name, $caption, $req, $comment, (int)(!!$value));
	}
	
	function validate($value) {}
	
	function __set($key, $value) {
		switch ($key) {
			case 'value' :parent::__set($key, (int)!!$value);
				break;
			default:
				parent::__set($key, $value);
		}
	}
	
}

class FFList extends FormField {
	private $options = array();
	
	function validate($value) {
		if (!isset($this->options[$value]))
			throw new EFormData('FFLIST.invalidkey', $this->name);
	}

	function __get($key) {
		switch ($key) {
			case 'options': return $this->options;
				break;
			default:
				return parent::__get($key);
		}
	}
	
	function __set($key, $value) {
		switch ($key) {
			case 'options': $this->options = $value;
				break;
			default:
				parent::__set($key, $value);
		}
	}
	
	function display() {
		$e = parent::display();
		foreach($this->options as $key=>$value) {
			$e->add(E('option', A('value', $key, 'caption', $value)));
		}
		return $e;
	}
}

class FFRadio extends FFList {}

class FFComoboBox extends FFList {}

class FFDate extends FormField {}

class FFTime extends FormField {}

class FFInt extends FormField {
	function validate($value) {
		parent::validate($value);

		if (!preg_match('/^-?[0-9]+$/', $value))
			throw new EFormData('FFINT.NaN', $this->name);
	}	
}

class FFFloat extends FormField {}

class FFCurrency extends FFFloat {}

class FFFile extends FFList {

	private $minsize = false;
	private $maxsize = false;
	private $mimes = array();
	
	function validate($value) {
		
	}
}

class FGroup extends FormElement {
	
	private $name;
	private $caption;
	private $items = array();
	
	function __construct($caption, $name = '') {
		$this->name = $name;
		$this->caption = $caption;
	}
	
	function add($el) {
		$el->form = $this->form;
		if ($el instanceof FormField) $this->form->fields->add($el);
		$this->items[] = $el;
	}
	
	function display() {
		$e = E('group', A('caption', $this->caption, 'name', $this->name));
		foreach($this->items as $item) $e->add($item->display());
		return $e;
	}
}


?>