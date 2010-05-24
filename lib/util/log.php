<?php
namespace FW\Util;

class Log extends \FW\Object {
	private $name;
	protected $handle;
	private $email;
	private $db;
	
	function __construct($name) {
		Validator::validate($name, 'latintext');
		$this->name = $name;
	}

	function __get($key) {
		switch($key) {
			case 'name' : return $this->name;
			default:
				parent::__set($key, $value);
		}
	}

	function __destruct() {
		if ($this->handle) fclose($this->handle);
	}
	
	function write($str) {
		file_put_contents('php://stderr', $str.PHP_EOL);
	}
}

class LogDB extends Log {
	
	function __construct($name, $db = false) {
		parent::__cinstruct($name);
		if ($db) {
			if (is_object($db)) $this->handle = $db;
			else $this->handle = DB::connect($db);
		}
		else $this->handle = App::$instance->db;
	}
	
	function write($str) {
		$this->handle->execf("INSERT INTO ".FW_TBL_LOG." (createdate, name, message) VALUES (NOW(), :0, :1)", $this->name, $str);
	}
}

class LogFile extends Log {
	
	function __construct($name, $path = false) {
		parent::__cinstruct($name);
		if (!$path) $path = FW_PTH_LOG;
		$this->handle = new File("$name.log", $path);
	}
	
	function write($str) {
		$this->handle->write(date('[Y/m/d H:i:s] ').str_replace("\n", "\n\t", $str)."\n\n");
	}
}

class LogMail extends Log {
	private $email;

	function __construct($name, $address, $mt = false) {
		parent::__cinstruct($name);
		Validator::validate($address, "email");
		
		$this->email = $address;
		if ($mt) {
			if (is_object($mt)) $this->handle = $mt;
			else $this->handle = MailTransport::factory($mt);
		}
		else $this->handle = App::$instance->mt;
	}

	function write($str) {
		$l = new MailLetter();
		$l->text = $str;
		$l->to = $this->email;
		$l->subject = "LOG.$this->name";
		$this->handle->connect($l);
		$this->handle->send($l);
		$this->handle->close();
	}
	
}

?>