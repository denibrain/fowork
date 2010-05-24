<?php

class POP3 {
	private $server;
	public $response;
	
	function __construct() {
		$this->server = null;
		$this->response = '';
	}
	
	private function isOk() {
		return substr($this->respone, 0, 3) === '+OK';
	}
	
	public function connect($server = '127.0.0.1', $port = '110') {
		if (!$this->server = @fsockopen("tcp://$server", 110))
			throw new Exception("Cannot open $server:$port");
			
		$this->response = trim(fgets($this->server, 512));
		
	}
	
	function send($cmd) {
		fwrite($this->server, "$cmd\r\n");
		// get response;
	}
	
	function retr($id) {
		$this->send("RETR $id");
		$body = '';
		while (($line = fgets($this->server, 512)) != ".\r\n") {
			$body .= $line;
		}
		return $body;
		
	}

	function rset() {
		$this->send('RSET');
	}

	function quit() {
		$this->send('QUIT');
		fclose($this->server);
	}

	function noop() {
		$this->send('NOOP');
	}
	
	function delete($id) {
		$this->send("DELE $id");
	}

	function stat() {
		$this->send("STAT");
	}

	function auth($user, $pass) {
		$this->send("USER $user");
		$this->send("USER $pass");
	}

	function list() {
		$this->send("LIST");
		$messages = array();
		while (($line = fgets($this->server, 512)) != ".\r\n") {
			list($id, $size) = explode(' ', $line);
			$messages[$id] = trim($size);
		}
		return $messages;
	}
	
	function getMessage($id) {
		$this->send("LIST $id");
		return $this->response;
	}
}
?>