<?php
namespace FW\Net\Sockets;

class Server extends \FW\Object {
	
	private $handle; // server socket
	private $connected; // connection state
	private $connections;
	private $socks;
	
	private $stopState = false;
	
	public $onNewConnection;
	public $onIdle;
	
	public function __construct() {
		$this->connected = false;
		$this->handle = NULL;
	}
	
	public function run($host = '127.0.0.1', $port = 1000) {
		$this->connect($host, $port);
		try {
			$this->doServe();
		} catch(\Exception $e) {
			if ($this->connected) $this->disconnect();
			throw $e;
		}
	}
	
	//public isSetHandler
	protected function connect($host, $port) {
		if(($this->handle = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) == false){
			$this->close();
			throw new ESocketServer(socket_strerror(socket_last_error()), socket_last_error());
		}
		if(socket_bind($this->handle, $host, $port) == false){
			$this->close();
			throw new ESocketServer(socket_strerror(socket_last_error()), socket_last_error());
		}
		if(socket_listen($this->handle, 10000) == false){
			$this->close();
			throw new ESocketServer(socket_strerror(socket_last_error()), socket_last_error());
		}
		
		socket_set_option($this->handle, SOL_SOCKET, SO_REUSEADDR, 1);
		socket_set_option($this->handle, SOL_SOCKET, SO_KEEPALIVE, 1);
		socket_set_nonblock($this->handle);
		$this->connections = array();
		$this->socks = array();
		$this->connected = true;
	}
	
	
	protected function getId($res) { return substr((string)$res, 13); }

	private function close() { 
		@socket_shutdown($this->handle, 2);
		@socket_close($this->handle);
		$this->handle = null;
		$this->connected = false;
	}
	
	protected function disconnect() {
		//... check open connections
		$this->close();
	}
	
	private function checkNewConnections() {
		$r = array($this->handle);
		$connCount = socket_select($r, $w = null, $e = null, 0);	#
		if($connCount === false){
			throw
			new ESocketServer(socket_strerror(socket_last_error()), socket_last_error());
		}
		if($connCount){
			$clientSocket = socket_accept($this->handle);
			if(!$clientSocket){
				throw
				new ESocketServer(socket_strerror(socket_last_error()), socket_last_error());
			}
			$socketId = $this->getId($clientSocket);
			$this->connections[$socketId] = call_user_func($this->onNewConnection, $clientSocket);
			$this->socks[$socketId] = $clientSocket;
		}
	}

	function proceedConnections() {
		if (!$this->socks) return;
		$r = $this->socks;
		$w = null;
		$e = $this->socks;
		$connCount = socket_select($r, $w, $e, 3);
		if ($connCount === false) {
			throw
			new ESocketServer(socket_strerror(socket_last_error()), socket_last_error());
		}
		elseif ($connCount) {
			foreach ($r as $sock) {
				
				$id = $this->getId($sock);
				try{
					$keepAlive = $this->connections[$id]->proceed();
					
					if($keepAlive === false){
						$this->dropSocket($id);
					}
					elseif($keepAlive === null){
						$this->stopServer();
					}
					
				}catch(ESocketServer $ex){
					$this->dropSocket($id);
					throw $ex; #
				}
			}
			if (count($e)) {
				throw
				new ESocketServer(socket_strerror(socket_last_error()), socket_last_error());
			}
		}
	}
	
	private function doServe() {
		while(true) {
			if(!$this->stopState) {
				$this->checkNewConnections();
			}


			if(count($this->connections)) {
				$this->proceedConnections();
			}
			
			if (isset($this->onIdle)) {
				\call_user_func($this->onIdle);
			}
			usleep(100);
		}
	}
	
	public function dropSocket($id)	{
		if(isset($this->connections[$id])){
			unset($this->connections[$id]);
		}
		if(isset($this->socks)){
			unset($this->socks[$id]);
		}
		@socket_shutdown($socket, 2);
		@socket_close($socket);
	}
	
	public function stopServer() {
		$this->stopState = true;
	}
	
}


class ESocketServer extends \Exception {}
