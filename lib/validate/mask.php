<?php
namespace FW\Validate;

class Mask extends Validator {

	const TEXTLINE = "[\\\\\\/()\"!+:;'¹,.0-9A-Za-z\x7F-\xFF\\n\\r -]+$/";
	const TEXT = "/^[\\n\\r\\t*\\/()\"!:;'¹,.0-9A-Za-zà-ÿÀ-ß -]+$/s";
	const INT = "/^[0-9]+$/";
	const LATIN = "/^[a-zA-Z]+$/";
	const LATINTEXT = "/^[a-zA-Z-\\s]+$/";
	const NUMLATIN ="/^[0-9a-zA-Z-\\s]+$/";
	const LETTERS = "/^([a-zà-ÿÀ-ß¸¨]+$/i";
	const LETTERSEX = "/^([a-zà-ÿÀ-ß¸¨-]+$/i";

/*		"date"=>"[0-9]{2}[.-][0-9]{2}[.-][0-9]{4}",
		"phone"=>RE_PHONE,
		"phones"=>RE_PHONES,
		"email"=> array(RE_MAILBOX, '', 'i'),
		"emails"=> array(RE_MAILBOXES, '', 'i'),
		"domain" => array(RE_DOMAIN,'', 'i'),
		"domain2level" => array(RE_DOMAIN2LEVEL,'', 'i'),
		"www" => array(RE_WWW,'', 'i'),
		"ip" => RE_IP*/


	private $mask;

	function __construct($mask = Mask::TEXT) {
		$this->mask = $mask;
	}

	function validate($value) {
		if (!preg_match($this->mask, $value)) 
			throw new EValidate("Mask.mask");
	}

}