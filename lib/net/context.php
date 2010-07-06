<?php
namespace FW\Net;

class Context
{
	protected $socket;
	
	function __construct($socket) {
		$this->socket = $socket;
	}
	
	public function proceed()
	{
		
	}
	
	public function read()
	{
		$buf = '';
		$buf = @socket_read($this->socket, 0xffff);
		if($buf === false){
			$eCode = socket_last_error();
			throw new ESocketServer(socket_strerror($eCode), $eCode);
		}
		
		return $buf;
	}
	
	public function write($buf)
	{
		if(@socket_write($this->socket, $buf) === false){
			$eCode = socket_last_error();
			throw new ESocketServer(socket_strerror($eCode), $eCode);
		}
	}
	
	public function close()
	{
		
	}
	
	public function getSocket()
	{
		return $this->socket;
	}
	
}

















