<?php
namespace FW\App;

class ConsoleApp extends App {
	public $script;
	public $params;

	public function __construct() {
		parent::__construct();

		global $argv;

		if (!isset($argv)) {
			die("This is command line tool");
		}
		
		$this->script = array_shift($argv);
		$this->params = $argv;
	}
}
?>