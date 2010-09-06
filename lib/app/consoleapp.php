<?php
namespace FW\App;

class ConsoleApp extends App {
	public $script;
	public $params;

	public function __construct($root = false) {
		parent::__construct($root);

		global $argv;

		if (!isset($argv)) {
			die("This is command line tool");
		}
		
		$this->script = array_shift($argv);
		$this->params = $argv;
	}

	function run() {
		__run($this);
	}
}
