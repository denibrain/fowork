<?php
namespace FW\Log;
use FW\Validate\Mask;

class Log extends \FW\Object {
	private $name;
	protected $handle;
	private $email;
	private $db;
	
	static private $mask;
	
	static function init() {
		self::$mask = new Mask(Mask::LATIN);
	}
	
	function __construct($name) {
		self::$mask->validate($name);
		$this->name = $name;
	}

	function getName() {return $this->name;}

	function __destruct() {
		if ($this->handle) fclose($this->handle);
	}
	
	function write($str) {
		file_put_contents('php://stderr', $str.PHP_EOL);
	}
}

Log::init();