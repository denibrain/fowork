<?php
namespace FW\VCL\Filter;

class Filter extends \FW\VCL\Component {
	
	public $value;
	private $grid;
	
	function __construct($name) {
		parent::__construct($name);
		$this->family = 'filter';

		if (isset($_GET['filter'])) $this->params['filter'] = $_GET['filter'];
	}

	function display() {
		$skeleton = parent::display();
		$skeleton->add(\D($this, 'value'));
		return $skeleton;
	}
}