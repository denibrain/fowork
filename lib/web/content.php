<?php
namespace FW\Web;

class ContentHeaders implements \IteratorAggregate  {
	private $items = array();
	
	function __get($key) {
		return $this->items[$key];
	}

	function __set($key, $value) {
		$this->items[$key] = $value;
	}
	
	function getIterator() {
		return new \ArrayIterator($this->items);
	}
}

class Content extends \FW\Object {

	protected $type = 'html';
	protected $encode = '8bit';
	private $headers;
	private $code = 200;
	private $body = '';
	
	
	static public $contentTypes = array(
		'xml' => 'text/xml',
		'text' => 'text/plain',
		'html' => 'text/html',
		'css' => 'text/css'
	);
	
	function __construct($type = 'html') {
		$this->type = $type;
		$this->headers = new ContentHeaders();
	}
	
	function __get($key) {
		switch($key) {
			case 'code': return $this->code;
			case 'body': return $this->body;
			case 'headers': return $this->headers;
			default: return parent::__get($key);
		}
	}
	
	function __set($key, $value) {
		switch($key) {
			case 'code': $this->code = (int)$value; break;
			case 'body': $this->body = $value;break;
			default:
				parent::__set($key, $value);
		}
	}
	
	function send() {
		header('HTTP/1.1'.$this->code);
		if (isset(Content::$contentTypes[$this->type])) {
			$type = Content::$contentTypes[$this->type];
			header('Content-type: '.$type);
		}
		header('Content-encode-type: '.$this->encode);
		foreach($this->headers as $key => $value) header("$key: $value");
		echo $this->body;
	}
}

?>