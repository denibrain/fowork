<?php
namespace FW\Net\Sockets;

class Context extends \FW\Object {
	protected $socket;

	public function getSocket()	{ return $this->socket;	}
	
	function __construct($socket) {
		$this->socket = $socket;
	}
	
	public function proceed(){}
	
	/**
	* пишет в сокет строку
	* @param String $buf
	*/
	public function read() {
		$buf = '';
		$buf = @socket_read($this->socket, 0xffff);
		if($buf === false){
			$eCode = socket_last_error();
			throw new ESocketServer(socket_strerror($eCode), $eCode);
		}
		
		return $buf;
	}
	
	/**
	* пишет в сокет строку
	* @param String $buf
	*/
	public function write($buf) {
		if(@socket_write($this->socket, $buf) === false){
			$eCode = socket_last_error();
			throw new ESocketServer(socket_strerror($eCode), $eCode);
		}
	}

	public function close(){
		socket_close($this->socket);
	}
}