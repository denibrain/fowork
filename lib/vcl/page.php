<?php
namespace FW\VCL;

class Page extends Component {

	private $caption;

	function __construct($name) {
		parent::__construct($name);
		$this->family = 'page';
	}

	function handleEvent($event, $data) {}

	function getCaption() { return $this->caption; }
	function getMap() { return array(); }
	function setContent($value) { $this->content = $value; }

	function init($params) {}
	function caption($params) { return 'Untitled'; }
	function map($params) { return array(); }

	// @todo Need thinking
	function run() {
		if ($_SERVER['REQUEST_METHOD'] == 'GET')
			$set = &$_GET;
		else
			$set = &$_POST;

		if (isset($set['_event']) && isset($set['_sender'])) {
			$this->perform($set['_sender'], $set['_event'], $set);
		}
	}
}