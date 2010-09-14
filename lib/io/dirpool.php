<?php

namespace fw\io;

class Dirpool extends \fw\Object {

	private $dirs;

	function __construct() {
		$this->dirs = array();
		foreach(\func_get_args() as $path) {
			$this->dirs[] = new Dir($path);
		}
	}

	function deleteFiles($mask) {
		foreach($this->dirs as $dir) {
			$dir->deleteFiles($mask);
		}
	}
}