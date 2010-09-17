<?php
namespace FW\Web;

class File extends Content {
	private $exists;
	private $properties;
	
	private $fnInfo;
	private $fnData;

	private $data;
	public $permissions;

	function __construct($Aname) {
		$this->fnInfo = $Aname.".info";
		$this->fnData = $Aname.".data";
		$this->type = 'text';
		$this->permissions = '';

		$this->exists = false;
		if (file_exists($this->fnInfo)) {
			if (file_exists($this->fnData)) {
				$this->exists = true;
				$this->readInfo();
			} else {
				if (!unlink($this->fnInfo)) throw new ESystem("Delete file {$this->fnInfo}");
			}
		} else {
			if (file_exists($this->fnData)) {
				if (!unlink($this->fnData)) throw new ESystem("Delete file {$this->fnData}");
			}
		}
	}

	// read .info file, get properties of content
	private function readInfo() {
		$f = fopen($this->fnInfo, 'r');
		if (!$f) throw \Exception("Cannot read {$this->fnInfo}");
		$lineNo = 0;
		while ($line = fgets($f, 4096)) {
			if (++$lineNo > 20) throw new \Exception("InfoFile is too large {$this->fnInfo}");
			$line = trim($line);
			if (!$line) continue;
			$pos = strpos($line, '=');
			if ($pos === false) throw new \Exception("Invalid info on line $lineNo in  {$this->fnInfo}");
			$paramName = substr($line, 0, $pos);
			$paramValue = substr($line, $pos + 1);
			if ($paramName == 'type') {
				if (!isset(Content::$contentTypes[$paramValue])) throw new \Exception("FI Invalid contentType[$paramValue]. line $lineNo in {$this->fnInfo}");
				$this->type = $paramValue;
			}
			else
			if ($paramValue == 'permissions') {
				$this->permissions = $paramValue;
			}
			// TODO need check
			$this->properties[$paramName] = $paramValue;
		}
		fclose($f);
	}

	// write properties
	private function writeInfo() {
		$c = date('Ymd');
		file_put_contents($this->fnInfo, "type=$this->type\npermissions=$this->permissions\ncreated=$c\n");
	}

	function write() {
		$this->writeInfo();
		file_put_contents($this->fnData, $this->data);
	}

	function output() {
		if (User::checkPermissions($this->permissions, PERM_READ)) throw new E401();
		if (!$this->exists) throw new E404();
		
		readfile($this->fnData);
	}
	
	function __get($key) {
		switch ($key) {
			case 'exists': return $this->exists;
			case 'data': return $this->data = file_get_contents($this->fnData);
			case 'properties': return $this->properties;
			default: return parent::__get($key);
		}
	}
	
	function __set($key, $value) {
		switch ($key) {
			case 'data': $this->data = $value;
				break;
			default: return parent::__set($key, $value);
		}
	}
		
}
?>