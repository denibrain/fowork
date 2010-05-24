<?php
namespace FW\Text;

class ParametricTemplate extends Template {
	
	private $text = '';
	private $params = array();
	private $insets = array();
	private $defvresolver = NULL;
	
	public function __construct($file = false, $defvresolver = false) {
		$this->defvresolver = $defvresolver;
		parent::__construct($file);
	}
	
	public function __set($key, $value) {
		if (isset($this->params[$key])) $this->params[$key] = $value;
		else throw new \Exception("No param with name '$key'");
	}
	
	public function __get($key) {
		if (isset($this->params[$key])) return $this->params[$key];
		else throw new \Exception("No param with name '$key'");
	}
	
	public function setText($text) {
		$this->insets = array();
		$this->params = array();
		$f = $this->defvresolver;
		if (preg_match_all('/{([a-z](?:[a-z]|-[a-z])*)(?::([^}]+))?}/', $text, $regs, PREG_SET_ORDER )) {
			foreach($regs as $param) if (!isset($this->insets[$param[0]])) {
				$inset = array_shift($param);
				$this->insets[$inset] = $param;
				if (isset($param[1]) && isset($f)) $this->insets[$inset][1] = $f($param[0],$param[1]);
				$this->params[$param[0]] = false;
			}
		}
		$this->text = $text;
	}

	public function __invoke($values = array()) {
		return $this->compile($values);
	}
	
	public function compile($values = array()) {
		if (!$this->params)	return $this->text;
		$a = array();
		$b = array();
		foreach($this->insets as $key => $info) {
			$a[] = $key;
			$name = $info[0];
			$b[] = isset($values[$name]) ? $values[$name] : (
				false!==$this->params[$name] ? $this->params[$name] : (
					isset($info[1]) ? $info[1] : ''
				)
			);
		}
		return str_replace($a, $b, $this->text);
	}
	
	public function __toString() {
		return $this->compile();
	}
}

?>