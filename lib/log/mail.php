<?php
namespace FW\Log;

class Mail extends Log {
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
