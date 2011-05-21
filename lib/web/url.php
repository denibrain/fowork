<?php
namespace FW\Web;

/*
 @property string $address The real address. Readonly.
 @property array $domain Domains of the address. Readonly.
 @property string $mask The mask of address. Readonly.
 @property array $maskdomain Domains of the mask of address. Readonly.
*/
class URL extends \FW\Object {
	private $domain = array();
	private $maskdomain = array();
	
	function __construct($u = false) {
		if ($u===false)
			$u = isset($_GET[FW_URL_VARIABLE])?$_GET[FW_URL_VARIABLE]:'';
		$this->setAddress($u);
	}
	
	function __toString() {
		return implode(".", $this->domain);
	}
	
	function Local($level) {
		if ($level < 0)
			throw new \Exception("Invalid level value");
		return new URL(implode(".", array_slice($this->domain, $level)));
	}

	function parentUrl($level = 1) {
		if ($level < 0)
			throw new \Exception("Invalid level value");
		return new URL(implode(".", array_slice($this->domain, 0, -$level)));
	}

	function setAddress($u) {
		if ($u && !preg_match('"(?:[a-z_.0-9]+/)*[a-z_.0-9]+"i', $u))
			throw new \Exception("Invalid url");

		$this->domain = explode(".", $u);
		$this->maskdomain = array_map(
			function($e) {
				return preg_match('/^([0-9]+)|-([a-z0-9]+)$/', $e) ? '*' : $e;
			}, $this->domain);		
	}
	
	function __set($key, $value) {
		switch ($key) {
			case 'address': return $this->setAddress($value);
			default:
				return parent::__set($key);
		}
	}
	
	function __get($key) {
		switch($key) {
			case 'maskdomain': return $this->maskdomain;
			case 'domain': return $this->domain;
			case 'mask': return implode(".", $this->maskdomain);
			case 'address': return implode(".", $this->domain);
			default:
				return parent::__get($key);
		}
	}
}
