<?php
namespace FW\Net\Sockets;

class Message extends \FW\Object {
	/**
	 * LOGIN
	 * \n
	 * ContentLength: 898
	 * \n
	 * \n
	 * <?xml version="1.0" encoding="UTF-8"?>
	 * <request>
	 * 	<login name="name" password="password"/>
	 * </request>
	 **/
	/**
	 * OK
	 * \n
	 * ContentLength:53
	 * \n
	 * \n
	 * <?xml version="1.0" encoding="UTF-8"?>
	 * <response>
	 * 	<login status='access allowed'/>
	 * </response>
	 **/

	public $body;
	public $headers;
	public $type;
	
	function __construct($type = 'WAZZUP', $bodyRootName = 'request') {
		$this->type = $type;
		$this->body = E($bodyRootName);
		$this->headers = new \FW\Net\Header();
	}

	function getBody() { return $this->body; }
	function getHeaders() { return $this->headers; }
	function getType () { return $this->type; }
	
	public function __toString() {
		$this->headers->ContentLength = strlen($text =
			'<?xml version="1.0" encoding="UTF-8"?>'.$this->body->asXML());
		return T(
			$this->type.PHP_EOL.
			((string)$this->headers).
			$text
		)->seteol();
	}

	/**
	 *@param string $text текст запроса
	 *@throws EIncomingMessage
	 **/
	public function parse($text) {
		$text = T($text)->seteol();
		$bodyHead = explode(PHP_EOL.PHP_EOL, $text, 2);
		if(count($bodyHead) != 2){
			throw new EIncomingMessage('No head/body separator', EIncomingMessage::parseError);
		}
		$head = $bodyHead[0];
		$this->body = $bodyHead[1];
		
		$strPairs = explode(PHP_EOL, $head);
		if(isset($strPairs[0]) and strlen($strPairs[0])){
			$this->type = \strtoupper($strPairs[0]);
		}
		
		foreach($strPairs as $strPair){
			$arrPair = explode(':', $strPair, 2);
			if(count($arrPair) != 2) continue;
			$name = trim($arrPair[0]);
			$value = trim($arrPair[1]);
			if(!strlen($name) or !strlen($value)) continue;
			$this->headers->$name = $value;
		}
		
		//Content-length check
		if($this->headers->ContentLength){
			$contentLength = $this->headers->ContentLength->value;
			if(is_numeric($contentLength)){
				$contentLength = intval($contentLength);
				if($contentLength !== strlen($this->body)){
					throw new EIncomingMessage('Incorrect value of the "ContentLength" header', EIncomingMessage::parseError);
				}
			}else{
				throw new EIncomingMessage('The "ContentLength" header must be numeric'.$this->headers->ContentLength, EIncomingMessage::parseError);
			}
		}else{
			throw new EIncomingMessage('No "ContentLength" header', EIncomingMessage::parseError);
		}

		$this->body = E($this->body);
	}
}

class EIncomingMessage extends \Exception {
	const parseError = 400;
	const unknownContentType = 401;
}