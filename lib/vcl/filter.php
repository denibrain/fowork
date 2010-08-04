<?php
namespace FW\VCL;

class Filter extends \FW\VCL\Component {
	
	public $filter;
	
	function __construct($name) {
		parent::__construct($name);
		$this->family = 'filter';
	}

	function display() {
		$skeleton = parent::display();
		$skeleton->add(\D($this, 'filter'));
		return $skeleton;
	}

	function doFilter($data) {
		if (isset($data['filter']))
			$this->filter = $data['filter'];
	}
}