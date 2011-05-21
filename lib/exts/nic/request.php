<?php
namespace FW\Exts\Nic;

/**
 * Description of request
 *
 * @author a.garipov
 */
class Request extends \FW\Object {
	private $headers;
	private $groups;
	
	function __construct() {
		$this->headers = new HashMap;
		$this->groups = new HashMap('Group');
	}

	public function getHeaders() {return $this->headers;}
	public function getParams()	{return $this->params;}
	public function getGroups()	{return $this->groups;}

	function addGroup($name) {
		$g = $this->groups[] = new Group();
		$g->title = $name;
		return $g;
	}

	public function __toString() {
		$string = '';

		//header
		foreach($this->headers->getArray() as $name => $value){
			$string .= ($name.':'.$value."\n");
		}
		$string .= "\n";

		//body
		foreach($this->groups->getArray() as $group){
			$string .= ('['.$group->title."]\n");
			foreach($group->getArray() as $name => $value){

				$lines = array();
				$oldLines = explode("\n", $value);
				foreach($oldLines as $line){
					foreach(explode("\n", wordwrap($line, 50, "\n", true)) as $v){
						$string = $string.$name.':'.$v."\n";
					}
				}
			}
			$string .= "\n";
		}

		return $string;
	}
}