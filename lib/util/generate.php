<?php 
namespace FW;

class Password extneds Object {
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
			$a = array('a', 'e', 'u', 'i', 'o');
			$l = $a[rand(0, 4)];
			if (rand(0, 10) > 5) return strtoupper($l);
			else return $l;
		}
		elseif ($type == 'S') {
			$b = array('q', 'w', 'r', 't', 'y', 'p', 's', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'z', 'x', 'c', 'v', 'b', 'n', 'm');
			$l = $b[rand(0, 20)];
			if (rand(0, 10) > 5) return strtoupper($l);
			else return $l;
		}
		elseif ($type == 'X') {
			$c = array('_', '-', '%', '#', '@', '!');
			$l = $c[rand(0, 5)];
			if (rand(0, 10) > 5) return strtoupper($l);
			else return $l;
		}
		elseif ($type == 'N') {
			return rand(0, 9);
		}
	}

	private function schemaPass($schema) {
		$l = strlen($schema);
		$code = '';
		for($i=0;$i<$l;++$i) $code.= codePass($schema[$i]);
		return $code;
	}

	public function generate() {
		$a = explode(" ",microtime());
		srand($a[0]*$a[1]);
		$schemas = array('AX', 'SSG', 'SG', 'SGS', 'N', 'NN', 'NNN');
		$code = '';
		while (strlen($code) < $this->len) $code .= schemaPass($schemas[rand(0,6)]);
		return $this->code = $code;
	}
	
	public function __toString() {
		return $this->code;
	}
}
?>