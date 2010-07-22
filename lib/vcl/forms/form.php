<?php
namespace FW\VCL\Forms {

class Form extends \FW\VCL\Component implements \ArrayAccess {
	const OK = 1;
	const NONE = 0;
	const ERROR = 2;
	const REFRESH = 3;

	private $action;
	private $method = 'POST';
	private $caption;
	private $pressedButton;
	private $detectRefresh = false;
	
	private $status = Form::NONE;
	
	private $fields;
	private $buttons;
	private $items = array();
	private $id;
	
	public $onCreate = null;
	public $onFirstShow = null;
	public $onCheck = null;
	public $onSuccess = null;
	public $onRefresh = null;
	public $onError = null;
	
	private $errors;
	public $owner;
	public $autoProceed = false;
	public $responce;

	public function offsetExists($offset) { return $this->fields->offsetExists($offset); }
	public function offsetGet($offset) { return $this->fields->$offset->value; }
	public function offsetSet($offset, $value) { $this->fields->$offset->value = $value; }
	public function offsetUnset($offset) { throw new \Exception("Cannot delete field"); }

	function __construct($name, $elements = '', $owner) {
		parent::__construct($name);
		$this->fields = new FormFields();
		$this->buttons = new FormFields();
		$this->owner = $owner;
		if ($elements) include $elements;
		if ($this->onCreate) call_user_func($this->onCreate, $form);
	}
	
	function add(FormElement $e) {
		$e->form = $this;
		$f = "\FW\VCL\Forms\FormField";
		$b = "\FW\VCL\Forms\Button";
		if ($e instanceof $f) $this->fields->add($e);
		elseif ($e instanceof $b) $this->buttons->add($e);
		return $this->items[] = $e;
	}
	
	static function handleForm() {
		if (!isset($_POST['_form'])) return null;
		list($m, $f) = explode('.', $_POST['_form']);
		return \FW\App\App::$_->mm->$m->form($f);
	}
		
	function proceed($auxData = false) {
		if (false!==$auxData || (isset($_POST['_form']) && $this->name == $_POST['_form'])) {
			if (false===$auxData) {
				$auxData =  $this->method == 'POST' ?
					array_merge($_POST, $_FILES):$_GET;
				$this->id = $_POST['_id'];
			}
			else
				$this->id = false;
				
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
				if ($this->id && $this->detectRefresh &&
					$_SESSION['form'][$this->name]['id'] === $this->id
				) {
					$this->status = Form::REFRESH;
					if ($this->onRefresh) $this->responce = call_user_func($this->onRefresh, $this);
				}
				if ($this->pressedButton->type == 'submit')
					$this->status =  $this->responce = $this->check($auxData) ? Form::OK : Form::ERROR;
				else
					$this->status = Form::OK;
			}
		} else {
			$this->status = Form::NONE;
			if ($this->onFirstShow) $this->responce = call_user_func($this->onFirstShow, $this);
		}

		if ($this->status != Form::OK)
			$_SESSION['form'][$this->name]['id'] = $this->id = md5(microtime());
			
		switch ($this->status) {
			case Form::OK:
				if ($this->onSuccess) $this->responce = call_user_func($this->onSuccess, $this);
				break;
			case Form::ERROR:
				if ($this->onError) $this->responce = call_user_func($this->onError, $this);
				break;
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
			if (isset($this->onCheck)) call_user_func($this->onCheck, $this);
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
			  'id', $this->id,
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
	class EFormUser extends \FW\VCL\Forms\EFormData {
		function EFormUser($message, $field = '.common', $code = 'FF.user') {
			parent::__construct($code, $field, $message);
		}
	}
}
?>