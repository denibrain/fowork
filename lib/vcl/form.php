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
	
	private $action;
	private $method = 'POST';
	private $caption;
	
	private $buttons;
	
	private $status = FW_FS_NONE;
	
	private $fields;
	private $items = array();
	
	public  $userCheck = NULL;
	private $errors;

	function __construct($name, $elements = '') {
		parent::__construct($name);
		$this->fields = new FormFields();
		if ($elements) include $elements;
	}
	
	function add(FormElement $e) {
		$e->form = $this; $c = "\FW\VCL\FormField";
		if ($e instanceof $c) $this->fields->add($e);
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
			$this->status =  $this->check($auxData) ? FW_FS_OK : FW_FS_ERROR;
		} else {
			$this->status = FW_FS_NONE;
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