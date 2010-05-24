<?php
namespace FW\Net;

define('FW_HTTP_BUFFSIZE', 8192);

class EHTTP extends \Exception {}

class HTTP extends \FW\Object {
	
	private $handle;
	private $response;
	private $sendData;
	private $f;
	
	function __construct($host, $port = 80) {
		
		$headers
		if (!($this->handle = curl_init($host)))
			throw new EHTTP("Cannot init http", 666);
			
		$f = fopen("aaa", "a");
//		$a[CURLOPT_HEADERFUNCTION] = array($this, 'readHeader');
		$a[CURLOPT_WRITEFUNCTION] = array($this, 'readData');
		$a[CURLOPT_READFUNCTION] = array($this, 'writeData');
		$a[CURLOPT_USERAGENT] =	'FW-HTTP 0.1a';
		$a[CURLOPT_INFILE] =	$f;
		$a[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1;
		$a[CURLOPT_BUFFERSIZE] = FW_HTTP_BUFFSIZE;
		$a[CURLOPT_HEADER] = true;
		$a[CURLOPT_PORT] = $port;
		
		//CURLOPT_HTTPGET
		//CURLOPT_PORT
		// 
		curl_setopt_array($this->handle, $a);
	}
	
	function __destruct() {
		curl_close($this->handle);
	}
	
	function send($method = 'GET', $data, $contentType = 'text/plain') {
		$this->sendData = $data;
		
		echo $handle = curl_copy_handle($this->handle);
		
		if ($method == 'GET') {
			$a[CURLOPT_HTTPGET] = true;
			if (!$data) $a[CURLOPT_NOBODY] = true;
		} else {
			$a[CURLOPT_POST] = true;
		}

		$a[CURLOPT_HTTPHEADER] = array();
		$a[CURLOPT_HTTPHEADER][] = 'Content-Type: '.$contentType;

		
		curl_setopt_array($handle, $a);
		
		$e = false;
		if (!curl_exec($handle)) 
			$e = new EHTTP(curl_error($handle), curl_errno($handle));
		curl_close($handle);
		if ($e) throw $e;
	}
	
	function readHeader($url, $header) {
		print_r($header);
		return strlen($header);
	}

	function readData($url, $data) {
		//$this->data .= $data;
		print_r($data);
		return strlen($data);
	}
	
	function writeData($url, $fd, $length) {
		print_r(func_get_args());
		$l = strlen($this->sendData);
		if ($l == 0) return 0;
		if ($l > $length) {
			$data = substr($this->sendData, 0, $length);
			$this->sendData = substr($this->sendData, $length);
			$l=$length;
			
		} else {
			$data = $this->sendData;
			$this->sendData = '';
		}
		fwrite($fd, $data);
		echo "writeee...$data [$length]";
		return $l;
	}	
}

?>