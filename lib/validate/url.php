<?php
namespace FW\Validate;

class URL extends Mask {

	const RELATIVE = 0;
	const FULL = 1;

	private $type;

	function __construct($type = URL::FULL) {
		$this->type = $type;
	}

	function validate($value) {
//		if ($this->type == URL::FULL) {
//			if (false!== ($pos = strpos('://'))) {
//				$protocol = substr()
//			}
//		}
		return true;
	}
/*
define('RE_WWW', 
define('RE_URL', '[a-z0-9]+:\/\/(?:[a-zA-Z0-9_]+(?::[a-zA-Z0-9_]+)?@)?'.RE_DOMAIN.'(?:\/[a-zA-Z0-9_-]+)*(?:\/)?(?:#[a-zA-Z0-9]+)?');
*/

}
