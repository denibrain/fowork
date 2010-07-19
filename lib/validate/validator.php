<?php
namespace FW\Validate;

/*
define('RE_WWW', '(?:https?://)?(?:www[.])'.RE_DOMAIN);
define('RE_URL', '[a-z0-9]+:\/\/(?:[a-zA-Z0-9_]+(?::[a-zA-Z0-9_]+)?@)?'.RE_DOMAIN.'(?:\/[a-zA-Z0-9_-]+)*(?:\/)?(?:#[a-zA-Z0-9]+)?');
*/

abstract class Validator {
	public abstract function validate($value);
}