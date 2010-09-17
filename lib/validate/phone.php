<?php
namespace FW\Validate;

class Phone extends Mask {
	function __construct() {
		parent::__construct('/^(?:[+0][0-9]{1,4}\s+)?(?:\([0-9]{1,7}\)\s*)?[ 0-9-]{3,}$/');
	}
}