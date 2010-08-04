<?php
namespace FW\VCL;

class Status extends Component {
	private $options;
	private $value;

	function __construct($name, $value = '', $options = array()) {
		parent::__construct($name);
		$this->family = 'status';
		$this->value = $value;
		$this->options = $options;
	}

	function customDisplay($skeleton) {
		$skeleton->add(A('value', $this->value), E('options', $this->options));
	}

	function getValue() {return $this->value;}
	function setValue($value) { $this->value = $value;}
	function getOptions() {return $this->options;}
	function setOptions($value) {$this->options = $value;}
}