<?php
namespace FW\Net\Sockets;

class Client extends \FW\Object {
	protected $socket;
	private $connected;
	protected $host;
	protected $port;

	function getHost() { return $this->host; }
	function setHost($value) { $this->host = $value; }
	function getPort() { return $this->port; }
	function setPort($value) { $this->port = $value; }
	
	function __construct() {}
	
	/**
	 * @throws ESocketClient
	 **/
	public function connect() {
		$this->socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
		if ($this->socket === false)
			throw new ESocketClient();
		
		if (socket_connect($this->socket, $this->host, $this->port) === false)
			throw new ESocketClient($this->socket);
		$this->connected = true;
	}
	
	/**
	 * @throws ESocketClient
	 * @todo refactor this
	 **/

	public function read() {
		$buf = @socket_read($this->socket, 0xffff);
		\FW\App\App::$_->log("[DBG] read:\n$buf");
		if ($buf === false) {
			throw new ESocketClient($this->socket);
		}
		return $buf;
	}

	/**
	 * @throws ESocketClient
	 **/
	public function write($buf)	{
		\FW\App\App::$_->log("[DBG] write:\n$buf");
		if (@socket_write($this->socket, $buf) === false)
			throw new ESocketClient($this->socket);
	}
	
	public function disconnect() {
		if (!$this->connected)	return;
		@socket_shutdown($this->socket, 2);
		@socket_close($this->socket);
	}
	
	function __destruct() {
		if (!$this->connected) return;
		$this->disconnect();
		$this->connected = false;
	}
}

class ESocketClient extends \Exception {
	function __construct($socket = false) {
		$eCode = $socket ? socket_last_error($socket) : socket_last_error();
		parent::__construct(socket_strerror($eCode), $eCode);
	}
}
