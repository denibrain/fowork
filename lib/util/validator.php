<?php
namespace FW\Util;

define('RE_INT', '\d+');
define('RE_DOMAINITEM', '(?:xn--)?[a-z0-9](?:-?[a-z0-9])*');
define('RE_DOMAIN2LEVEL', RE_DOMAINITEM.'(?:\.'.RE_DOMAINITEM.')*');
define('RE_DOMAIN', RE_DOMAINITEM.'(?:\.'.RE_DOMAINITEM.')*');
define('RE_WWW', '(?:https?://)?(?:www[.])'.RE_DOMAIN);
define('RE_MAILBOX', '[a-z0-9](?:[._-]?[a-z0-9])*@'.RE_DOMAIN);
define('RE_MAILBOXES', "(?:".RE_MAILBOX."; ?)*".RE_MAILBOX);
define('RE_PHONE', '(?:[+0][0-9]{1,4}\s+)?(?:\([0-9]{1,7}\)\s*)?[ 0-9-]{3,}');
define('RE_PHONES', "(?:".RE_PHONE."[;,]\s*)*".RE_PHONE);
define('RE_IPNO', '2(?:[0-4][0-9]|5[0-5])|1?[0-9]{2}');
define('RE_IP', '(?:'.RE_IPNO.')(?:\.(?:'.RE_IPNO.')){3}');
define('RE_NAME', '[a-zA-Z0-9_.-]+');
define('RE_DRIVE', '[a-zA-Z]:');
define('RE_FILENAME', RE_NAME.'(?:\/'.RE_NAME.')*');
define('RE_FULLFILENAME', '(?:'.RE_DRIVE.')?\/'.RE_FILENAME);
define('RE_FULLPATH', '(?:'.RE_DRIVE.')?\/(?:'.RE_FILENAME.')?');
define('RE_PASSWORD', '[a-zA-Z0-9!@#$%^&*()\[\]|+=\/\\\\_-]+');
define('RE_URL', '[a-z0-9]+:\/\/(?:[a-zA-Z0-9_]+(?::[a-zA-Z0-9_]+)?@)?'.RE_DOMAIN.'(?:\/[a-zA-Z0-9_-]+)*(?:\/)?(?:#[a-zA-Z0-9]+)?');
class EValidate extends \Exception {}; 

class Validator {
	public static $mask = array(
		"text"=>"[\\\\\\/()\"!+:;'№,.0-9A-Za-z\x7F-\xFF\\n\\r -]+",
		"textarea"=>array("[\\n\\r\\t*\\/()\"!:;'№,.0-9A-Za-zа-яА-Я -]+", '', 's'),
		"int"=>"[0-9]+",
		"latintext"=>"[a-zA-Z-\\s]+",
		"date"=>"[0-9]{2}[.-][0-9]{2}[.-][0-9]{4}",
		"phone"=>RE_PHONE,
		"phones"=>RE_PHONES,
		"email"=> array(RE_MAILBOX, '', 'i'),
		"emails"=> array(RE_MAILBOXES, '', 'i'),
		"domain" => array(RE_DOMAIN,'', 'i'),
		"domain2level" => array(RE_DOMAIN2LEVEL,'', 'i'),
		"www" => array(RE_WWW,'', 'i'),
		"kpp" => "[0-9]{9}",
		"inn10" => array('[0-9]{10}', 'INN10', ''),
		"inn12" => array('[0-9]{12}', 'INN12', ''),
		"ip" => RE_IP
	);

	public static function validate($value, $maskName) {
		if (!isset(self::$mask[$maskName])) 
			throw new \Exception("Mask $maskName not found"); 
		$mask = self::$mask[$maskName];
		$opt = ''; $checker = false;
		if (is_array($mask)) {
			list($mask, $checker, $opt) = $mask;
		} 
		if ($mask && !preg_match("/^$mask$/$opt", $value))
			throw new EValidate('Invalid value'."/^$mask$/$opt", 1);
		if ($checker) {
			$checker = "check$checker";
			self::$checker($value);
		}
	}

	private function checkINN10($inn) {
		$koef = array(2,4,10,3,5,9,4,6,8,0);
		if (strlen($inn) != 10) throw new EValidate("Длина ИНН должна быть 10 цифр");
		$sum = 0;
		for($i=0; $i<10; ++$i) $sum += $koef[$i] * $inn[$i];
		$no = $sum % 11;
		if ($no == 10) $no = $sum % 10;
		if ($no!=$inn[9]) 
			throw new EValidate("Введенный ИНН не существует", 2);
	}

	private function checkINN12($inn) {
		if (strlen($inn) != 12) throw new EValidate("Длина ИНН должна быть 12 цифр");
		$koef = array(7,2,4,10,3,5,9,4,6,8,0);
		$sum = 0;
		for($i=0; $i<11; ++$i) $sum += $koef[$i] * $inn[$i];
		$no = $sum % 11;
		if ($no == 10) $no = $sum % 10;
		if ($no!=$inn[10]) 
			throw new EValidate("Введенный ИНН не существует");
		
	
		$koef = array (3,7,2,4,10,3,5,9,4,6,8,0);
		$sum = 0;
		for($i=0; $i<12; ++$i) $sum += $koef[$i] * $inn[$i];
		$no = $sum % 11;
		if ($no == 10) $no = $sum % 10;
		if ($no!=$inn[11]) 
			throw new EValidate("Введенный ИНН не существует");
	}
}
?>