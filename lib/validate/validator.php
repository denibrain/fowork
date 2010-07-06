<?php
namespace FW\Validate;

/*
define('RE_INT', '\d+');
define('RE_IPNO', '2(?:[0-4][0-9]|5[0-5])|1?[0-9]{2}');
define('RE_IP', '(?:'.RE_IPNO.')(?:\.(?:'.RE_IPNO.')){3}');

define('RE_NAME', '[a-zA-Z0-9_.-]+');

define('RE_DRIVE', '[a-zA-Z]:');
define('RE_FILENAME', RE_NAME.'(?:\/'.RE_NAME.')*');
define('RE_FULLFILENAME', '(?:'.RE_DRIVE.')?\/'.RE_FILENAME);
define('RE_FULLPATH', '(?:'.RE_DRIVE.')?\/(?:'.RE_FILENAME.')?');

define('RE_PASSWORD', '[a-zA-Z0-9!@#$%^&*()\[\]|+=\/\\\\_-]+');

define('RE_WWW', '(?:https?://)?(?:www[.])'.RE_DOMAIN);
define('RE_URL', '[a-z0-9]+:\/\/(?:[a-zA-Z0-9_]+(?::[a-zA-Z0-9_]+)?@)?'.RE_DOMAIN.'(?:\/[a-zA-Z0-9_-]+)*(?:\/)?(?:#[a-zA-Z0-9]+)?');

	public static $mask = array(
		"date"=>"[0-9]{2}[.-][0-9]{2}[.-][0-9]{4}",
		"phone"=>RE_PHONE,
		"phones"=>RE_PHONES,
		"www" => array(RE_WWW,'', 'i'),
		"kpp" => "[0-9]{9}",
		"ip" => RE_IP
	);

*/


class Validator {
	public function validate($value) {}
}
?>