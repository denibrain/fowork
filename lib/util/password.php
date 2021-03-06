<?php 
namespace FW\Util;

class Password extends \FW\Object {
	private $code = '';
	private $len = 12;

	function __construct($len = 12) {
		$this->len = 12;
		$this->generate();
	}

	private function codePass($type) {
		if ($type == 'A') {
			$l = chr(ord('a') + rand(0, 25));
			if (rand(0, 10) > 5) return strtoupper($l);
			else return $l;
		}
		elseif ($type == 'G') {
			$a = 'aeuioAEUIO';
			return $a[rand(0, 9)];
		}
		elseif ($type == 'S') {
			$b = 'qwrtypsdfghjklzxcvbnmQWRTYPSDFGHJKLZXCVBNM';
			return $b[rand(0, 41)];
		}
		elseif ($type == 'X') {
			$c = '_-%#@!';
			return $c[rand(0, 5)];
		}
		elseif ($type == 'N') {
			return rand(0, 9);
		}
	}

	private function schemaPass($schema) {
		$l = strlen($schema);
		$code = '';
		for($i=0;$i<$l;++$i) $code.= $this->codePass($schema[$i]);
		return $code;
	}

	public function generate() {
		$a = explode(" ",microtime());
		srand($a[0]*$a[1]);
		$schemas = array('AX', 'SSG', 'SG', 'SGS', 'N', 'NN', 'NNN');
		$code = '';
		while (strlen($code) < $this->len) $code .= $this->schemaPass($schemas[rand(0,6)]);
		return $this->code = $code;
	}
	
	public function __toString() {
		return $this->code;
	}
}
?>