<?php
namespace FW\Validate;

class Domain extends Validator {
	const REAL = 1;
	const ANY = 0;
	const FQN = 2;

	private static $Mask;
	private $maxLevel = 0;
	private $minLevel = 1;
	private $type;

	static function init() {
		Domain::$Mask = new Mask('/^(?:xn--)?[a-z0-9](?:-?[a-z0-9])*$/i');		
	}

	function __construct($maxLevel = 0, $type = Domain::ANY) {
		$this->type = $type;
		if ($type == Domain::REAL)  $this->minLevel = 2;
		$this->maxLevel = $maxLevel;
	}
	
	function validate($value) {
		if (
			$value &&
			$this->type == Domain::FQN &&
			strrpos($value, '.') === strlen($value) - 1
		) {
			$value = substr($value, 0, -1);
		}
		$domains = explode('.', $value);
		if ($this->maxLevel && $this->maxLevel < count($domains))
			throw new EValidate("Domain.maxLevel");
		if ($this->minLevel && $this->minLevel > count($domains))
			throw new EValidate("Domain.minLevel");

		foreach($domains as $domain) 
			Domain::$Mask->validate($domain);
	}

	function setmaxLevel($value) {
		$this->maxLevel = (int)$value;
		if ($this->maxLevel && $this->maxLevel < $this->minLevel)
			$this->maxLevel = $this->minLevel;
	}

	function setminLevel($value) {
		$this->minLevel = (int)$value;
		if ($this->minLevel && $this->maxLevel && $this->maxLevel < $this->minLevel)
			$this->minLevel = $this->maxLevel;
	}
}

Domain::init();