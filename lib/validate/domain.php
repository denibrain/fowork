<?php
namespace FW\Validate;

class Domain extends Validator {
	const REAL = 1;
	const ANY = 0;

	static $Mask;
	private $maxLevel = 0;
	private $minLevel = 1;

	function __construct($maxLevel = 0, $type = Domain::ANY) {
		$this->type = $type;
		$this->maxLevel = $maxLevel;
	}
	
	function validate($value) {
		$domains = explode('.', $domain);
		if ($maxLevel && $maxLevel < count($domains))
			throw new EValidate("Domain.maxLevel");
		if ($minLevel && $minLevel > count($domains))
			throw new EValidate("Domain.minLevel");

		foreach($domains as $domain) 
			Domain::$Mask->validate($domain);
	}

}

Domain::$Mask = new Mask('/^(?:xn--)?[a-z0-9](?:-?[a-z0-9])*$/i');
