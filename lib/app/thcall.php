<?php
namespace FW\App;

class THCall extends \FW\Object {

	private $class;
	private $method;
	private $params = array();
	private $exParams = array();
	private $prefix;

	private $result = false;
	private $template = '';
	private $paramName;

	function __construct($exParams, $prefix) {
		$this->exParams = $exParams;   	
		$this->prefix = $prefix;
	}
	
	function init() {
		$this->result = false;
	}

	function proceed($type, $v, $pos, $proc) {
		switch($type){
			case 'a':
				$this->template = false;
				break;
			case 'vl':
				list($cl, $v) = explode(':', $v);
				if ($proc->state == 'expr') {
					$this->result =  App::$_->mm->$cl->$v;
					$this->template = "$cl.$v";
					$this->class = $v;
				}
				else
					$this->params[$this->paramName] = App::$_->mm->$cl->$v;
				break;
			case 'nm':
				if ($proc->state == 'eq') {
					$this->params[$this->paramName] = $v;
				}
				elseif ($proc->state == 'expr') {
					$this->template = $this->class = $v;
					$this->method = '';
				}
				else 
					$this->paramName = $v;
				break;
			
			case 'method':
				$this->method = substr($v, 1);
				$this->template .= $v;
				break;

			case 'var': $v = $this->exParams[substr($v, 1)];
			case 'num':
			case 'str2':
			case 'str1':
				$this->params[$this->paramName] = $v;
				break;
			case 'tpl':
				$this->template = $this->class . ($v == '@' ? '' : ".".substr($v, 1));
				break;
			case 'end':
				if (false===$this->result) {
					$class = $this->class;
					$method = $this->prefix.$this->method;
					$this->result = App::$_->mm->$class->$method($this->params);
				}
				break;
		}
	}

	function call() {
		return $this->result;
	}
	
	function content() {
		if ($this->template === false) return $this->result;
		return App::$_->transform($this->result, $this->template);
	}
	
	
}