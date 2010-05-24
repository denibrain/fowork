<?php
namespace FW\Text;

class Template {
	protected $name;
	// TODO WTF?
	protected $prepared;
	
	function __construct($file = false) {
		if ($file){
			$this->loadFromFile($file);
			$this->name = basename($file);
		}
		else
			$this->name = uniqid('tpl');
		
	}

	public function loadFromFile($filename) {
		if (file_exists($filename) && filesize($filename)< 100000)
			$this->setText(file_get_contents($filename));
		else throw new \Exception("Template $filename");
	}

	public function setText($text) {} // virtua; method	
	public function compile() {} // virtual method
}

?>