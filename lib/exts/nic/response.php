<?php
namespace FW\Exts\Nic;

/**
 * Объект ответа от клиента
 *
 * @author a.garipov
 * 
 * @property-read HashMap $headers
 * @property-read array $groups
 * @property-read HashMap $params
 *
 */
class Response extends \FW\Object {
	private $message;
	private $headers;
	private $groups;
	private $params;

	/**
	 * Создание
	 * @param string $message
	 */
	function __construct($message) {
		$this->message = substr($message, 0, -1);

		$this->headers = new HashMap('string');
		$this->params = new HashMap('string');
		$this->groups = array();
		
		$this->parse();

		$state = $this->headers['State'];
	}

	private function parse() {
		$message = strtr($this->message, array("\r" => '', "\x0A" => PHP_EOL));
		$bodyHead = explode(PHP_EOL.PHP_EOL, $message, 2);
		$this->parseText($this->headers, $bodyHead[0]);
		if(isset($bodyHead[1])) $this->parseText($this->params, $bodyHead[1]);
	}

	private function parseText($currentGroup, $text) {
		$bodyLines = explode(PHP_EOL, $text);
		foreach($bodyLines as $line){
			$line = trim($line);
			if($line == '') continue;

			if(substr($line, 0, 1) == '[' and substr($line, -1, 1) == ']'){
				$currentGroup = $this->groups[] = new Group();
				$currentGroup->title = substr($line, 1, -1);
			} else {
				$pair = explode(':', $line, 2);
				if(count($pair) == 2)
					$currentGroup[trim($pair[0])] = trim($pair[1]);
				else
					$currentGroup[] = $line;
			}
		}
	}

	public function getGroupsByTitle($title) {
		$groups = array();
		foreach($this->groups as $group)
			if($group->title == $title)
				$groups[] = $group;
		return $groups;
	}

	public function getGroupByTitle($title) {
		foreach($this->groups as $group)
			if($group->title == $title)
				return $group;
	}
	
	public function getHeaders() { return $this->headers; }
	public function getParams()	{ return $this->params;	}
	public function getGroups() { return $this->groups;	}
}

class EResponse extends Exception {}