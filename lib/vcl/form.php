<?php
namespace FW\VCL {

require "form.elements.php";

class FormFields extends \FW\Object implements \IteratorAggregate {
	private $items = array();

	function __get($key) {
		if (isset($this->items[$key])) return $this->items[$key];
		else return parent::__get($key);
	}
	
	function add($item) {
		if (isset($this->items[$item->name]))
			throw new \Exception("Field {$this->name} already exists");
		$this->items[$item->name] = $item;
	}
	
	function getIterator() {
		return new \ArrayIterator($this->items);
	}
}

class Form extends Component {
	const OK = 1;
	const NONE = 0;
	const ERROR = 2;
	
	private $action;
	private $method = 'POST';
	private $caption;
	private $pressedButton;
	
	private $status = Form::NONE;
	
	private $fields;
	private $buttons;
	private $items = array();
	
	public  $userCheck = NULL;
	private $errors;

	function __construct($name, $elements = '') {
		parent::__construct($name);
		$this->fields = new FormFields();
		$this->buttons = new FormFields();
		if ($elements) include $elements;
	}
	
	function add(FormElement $e) {
		$e->form = $this;
		$f = "\FW\VCL\FormField";
		$b = "\FW\VCL\FormButton";
		if ($e instanceof $f) $this->fields->add($e);
		elseif ($e instanceof $b) $this->buttons->add($e);
		return $this->items[] = $e;
	}
	
	static function handleForm() {
		if (!isset($_POST['_form'])) return null;
		list($m, $f) = explode('.', $_POST['_form']);
		return \FW\App\App::$instance->mm->$m->form($f);
	}
		
	function proceed($auxData = false) {
		if (false!==$auxData || (isset($_POST['_form']) && $this->name == $_POST['_form'])) {
			if (false===$auxData) {
				$auxData =  $this->method == 'POST' ?
					array_merge($_POST, $_FILES):$_GET;
			}
			$this->pressedButton = false;
			foreach($this->buttons as $name => $button) if (isset($auxData[$name])) {
				$this->pressedButton = $button;
			}
			if (!$this->pressedButton) {
				$this->errors[] = array('code'=> "FF.invalidbutton", 'field'=>".common",
					'text'=>S_FORM_INVALIDBUTTON);
				$this->status = Form::ERROR;
			}
			else {
				if ($this->pressedButton->type == 'submit')
					$this->status =  $this->check($auxData) ? Form::OK : Form::ERROR;
				else
					$this->status = Form::OK;
			}
		} else {
			$this->status = Form::NONE;
		}
		return $this->status;
	}
	
	function loadValues($data, $checkempty = false) {
		foreach($this->fields as $name => $field)
			if (isset($data[$name])) $field->value = $data[$name];
	}
	
	function getValues() {
		$v = array();
		foreach($this->fields as $key => $f) $v[$key] = $f->value;
		return $v;
	}

	function check($data) {
		$this->errors = array();
		foreach($this->fields as $key => $fld) {
			$fld->value = isset($data[$key]) ? $data[$key] : '';
			try {
				$fld->validate($fld->value);
			}
			catch (EFormData $e) {
				$this->errors[] = array('code'=> $e->code, 'field'=>$e->field, 'text'=>$e->getMessage());
			}
		}
		try {
			if (isset($this->userCheck)) call_user_func($this->userCheck, $this);
		}
		catch (EFormData $e) {
			$this->errors[] = array('code'=> $e->code, 'field'=>$e->field, 'text'=>$e->getMessage());
		}
		return !count($this->errors);
	}

	function display($data = array()) {
		$e = E('form',
			A('caption', $this->caption,
			  'name', $this->name,
			  'action', $this->action,
			  'method', $this->method));
		
		if ($data) $this->loadValues($data);
		
		foreach($this->items as $item) $e->add($item->display());
		if ($this->errors) {
			foreach($this->errors as $error) $e->add(E('error', $error));
		}
		return $e;
	}
	
	function __get($key) {
		switch ($key) {
			case 'name': return $this->name;
			case 'method': return $this->method;
			case 'status': return $this->status;
			case 'fields': return $this->fields;
			case 'pressedButton': return $this->pressedButton;
			
			default:
				parent::__get($key);
		}
	}	
}

}

namespace {
	class EFormUser extends \FW\VCL\EFormData {
		function EFormUser($message, $field = '.common', $code = 'FF.user') {
			parent::__construct($code, $field, $message);
		}
	}
}
?>