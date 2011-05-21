<?php
namespace FW\App;

class THResolve extends \FW\Object {
	private $params;
	private $exParams;
	private $text;
	private $prefix;
	private $method;
	private $class;
	private $paramName;

	function __construct($params, $prefix) {
		$this->exParams = $params;
		$this->prefix = $prefix;
	}
	
	function init() {
		$this->text = '';
		
	}
	function __get($key) {
		if ($key=='text') return $this->text;
		else return parent::__get($key);
	}
	
	function proceed($type, $v, $pos, $proc) {
		switch($type){
			case 'raw':
				$this->text.= $v;
				break;
			
			case 'vl':
				list($class, $v) = explode(':', $v);
				if ($proc->state == 'expr') 
					$this->text .= App::$_->mm->$class->$v;
				else 
					$this->params[$this->paramName] = App::$_->mm->$class->$v;
				break;
			
			case 'nm':
				if ($proc->state == 'eq')
					$this->params[$this->paramName] = $v;
				elseif ($proc->state == 'prms')
					$this->paramName = $v;
				elseif ($proc->state == 'expr') {
					$this->class = $v;
					$this->params = array();
				}
				break;
				
			case 'method':
				$this->method = substr($v, 1);
				break;
				
			case 'var':
				$v = $this->exParams[substr($v, 1)];
				if ($proc->state == 'text') $this->text .= $v;
				else $this->params[$this->paramName] = $v;
				break;	
			case 'num':
			case 'str2':
			case 'str1':
				$this->params[$this->paramName] = $v;
				break;
				
			case 'close':
				if ($proc->state=='vl')  break;
				$class = $this->class;
				$method = $this->prefix.$this->method;
				$this->text .= App::$_->mm->$class->$method($this->params);
				break;
		}
	}

}
?>