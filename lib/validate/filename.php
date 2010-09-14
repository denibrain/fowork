<?php
namespace FW\Validate;

class Filename extends Mask {

	const LOCALPATH = 0;
	const FULLPATH = 1;
	const LOCALFILENAME = 2;
	const FULLFILENAME = 3;

	const NAME = '[a-zA-Z0-9_.-]+';
	const DRIVE = '[a-zA-Z]:';

	private $type;

	function __construct($type = Filename::FULLPATH) {
		$this->type = $type;

		$mask = '';
		if ($type & 1)
			$mask = '(?:'.Filename::DRIVE.')?[/\\\\]';

		$mask .= '(?:'.Filename::NAME.'[/\\\\])*';

		if ($type & 2)
			$mask .= FileName::NAME;

		parent::__construct('`^'.$mask.'$`');
	}
}