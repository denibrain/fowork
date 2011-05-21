<?php
namespace FW\Web;

if (!defined('FW_URL_VARIABLE')) define('FW_URL_VARIABLE', 'u');
if (!defined('FW_URL_DEFAULT')) define('FW_URL_DEFAULT', '');

/*
 @property FW\Web\Url $url Url of request
 @property array $params GET values
 @property array $postdata POST values
 @property array $cookies COOKUE values
 @property string $method Request method : GET, POST
*/
class Request extends \FW\Object {
	private $url;
	private $params = array();
	private $postdata = array();
	private $cookies = array();
	private $method = 'GET';
	
	public function __construct($url = false) {
		$this->url = new URL(false!==$url?$url:
			(isset($_GET[FW_URL_VARIABLE])?$_GET[FW_URL_VARIABLE]:FW_URL_DEFAULT));
		if ($url === false) 
			$this->load();
	}
	
	private function load() {
		$this->method = $_SERVER['REQUEST_METHOD'];
		foreach($_COOKIE as $key=>$value)
			$this->cookies[$key] = $this->checkParam($value);
		foreach($_POST as $key=>$value)
			$this->postdata[$key] = $this->checkParam($value);
		foreach($_GET as $key=>$value)
			$this->params[$key] = $this->checkParam($value);
	}
	
	function __get($key) {
		switch ($key) {
			case 'url' : return $this->url;
			case 'params' : return $this->params;
			case 'postdata' : return $this->postdata;
			case 'cookies' : return $this->cookies;
			case 'method' : return $this->method;
			default:
				return parent::__get($key);
		}
	}
	
	function checkParam($value) {
		// TODO check value;
		return $value;
	}
	
}

?>