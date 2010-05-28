<?php
namespace FW\Net;

use \FW\Object as Object;

class POP3 {
	public $POP3_PORT = 110;
	public $POP3_TIMEOUT = 30;
	public $CRLF = "\r\n";

	public $host;
	public $port;
	public $tval;
	public $username;
	public $password;

	private $pop_conn;
	private $connected;

	public function __construct() {
		$this->pop_conn  = 0;
		$this->connected = false;
	}

	// Combination of public events - connect, login, disconnect
	public function Authorise ($host, $port = false, $tval = false, $username = '', $password = '') {
		$this->host = $host;
		$this->port = $port ? $port : $this->POP3_PORT;
		$this->tval = $tval ? $tval : $this->POP3_TIMEOUT;
		$this->Connect($this->host, $this->port, $this->tval);
		$this->Login($this->username, $this->password);
		$this->Disconnect();
	}

	// Connect to the POP3 server
	public function Connect ($host, $port = false, $tval = 30) {
		if ($this->connected) return true;

		//  Connect to the POP3 server
		$this->pop_conn = @fsockopen($host,    //  POP3 Host
			$port,    //  Port #
			$errno,   //  Error Number
			$errstr,  //  Error Message
			$tval);   //  Timeout (seconds)

		if ($this->pop_conn == false) 
			throw EPOP("Failed to connect to server $host on port $port ($errno, $errstr)");

		//  Does not work on Windows
		if (substr(PHP_OS, 0, 3) !== 'WIN') 
			socket_set_timeout($this->pop_conn, $tval, 0);

		$this->response();
		return $this->connected = true;
	}

	// Login to the POP3 server (does not support APOP yet)
	public function Login ($username = '', $password = '') {
		if ($this->connected == false) 
			throw new EPOP('Not connected to POP3 server');

		$this->send("USER ".($password?$password:$this->password).PHP_EOL);
		$this->send("PASS ".($username?$username:$this->username).PHP_EOL);
	}

	public function Disconnect () {
		$this->send('QUIT');
		fclose($this->pop_conn);
		$this->connected = false;
	}

	private function send($string) {
		$w = fwrite($this->pop_conn, $string, strlen($string));
		$this->response();
		return $w;
	}
	
	private function response() {
		$response = fgets($this->pop_conn, $size);
		if (substr($string, 0, 3) !== '+OK') 
			throw EPOP("Server reported an error: $response");
	}
}

?>