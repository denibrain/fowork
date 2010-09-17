<?php
namespace FW\Net;

define('FW_HTTP_BUFFSIZE', 8192);

class EHTTP extends \Exception {}

class Response extends \FW\Object {
	public $code;
	public $headers;
	public $body;
	
	function __toString() {
		return $this->body;
	}
	
}

class HTTP extends \FW\Object {
	
	private $host;
	private $port;
	private $path;
	private $protocol;
	private $connected;
	private $cookie;
	private $contentType = 'text/xml';
	private $socket;
	private $response;
	
	
	function __construct($url) {
		$p = parse_url($url);
		print_r($p);
		$this->host = isset($p['host'])?$p['host']:'127.0.0.1';
		$this->protocol = isset($p['scheme'])?$p['scheme']:'http';
		$this->port = isset($p['port'])?$p['port']:80;
		$this->path = isset($p['path'])?$p['path']:'/';
	}
	
	function __destruct() {
		if ($this->connected) $this->disconnect();
	}
	
	function connect() {
		$this->socket = socket_create (AF_INET, SOCK_STREAM, SOL_TCP);
		if (!socket_connect($this->socket, $this->host, $this->port)) {
			echo socket_last_error($this->socket);
			socket_clear_error($this->socket);
		}
		else
		$this->connected = true;
	}
	
	function disconnect() {
		socket_close($this->socket);
		$this->connected = false;
	}
	
	function send($wdata, $method = 'GET') {
		$headers = new Header();
		$headers->Host = "$this->host:$this->port";
		$headers->User_Agent = 'EPP Client/1.0';
		if ($this->cookie) $headers->Cookie = $this->cookie;
		$headers->Content_Type = $this->contentType."; charset=".FW_CHARSET;
		$headers->Content_Length = strlen($wdata);

		$senddata = "$method $this->path HTTP/1.1\r\n";
		$senddata.= $headers.$wdata;
		
		socket_write($this->socket, $senddata);
		$data = '';
		$buf = '                                  ';
		while (1) {
			$z = socket_recv($this->socket, $buf, 8192, MSG_WAITALL);
			$data.=$buf;
			if (!$z) break;
		}
		$pos = strpos($data, "\r\n\r\n");
		
		$headers = explode("\r\n", substr($data, 0, $pos));
		$res = new Response();
		$res->body = substr($data, $pos + 4);
		list(,$res->code) = explode(' ', array_shift($headers));
		$res->headers = new Header();
		foreach($headers as $h) {
			if (false !== ($pos = strpos($h, ':'))) {
				$name = substr($h, 0, $pos);
				if ($name == 'Set-Cookie') $this->cookie = trim(substr($h, $pos + 1));
				$res->headers->$name = trim(substr($h, $pos + 1));
			}
			else throw new \Exception("Unnamed header line $h");
		}

		echo "---- SEND\n".$senddata;
		echo "---- RECIEVE\n".$data;

		return $res;
	}
}

?>