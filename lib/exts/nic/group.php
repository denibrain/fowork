<?php
/**
 * Description of group
 *
 * @author a.garipov
 */
class Group extends HashMap {
	private $title;

	public function getTitle() { return $this->title; }
	public function setTitle($title) {$this->title = $title; }

	function setItem($key, $value) {
		if($key !== null){
			$key = trim($key);
		}
		if($value !== null){
			$value = trim($value);
		}
		
		if(isset($this->params[$key])){
			parent::setItem($key, parent::getItem($key)."\n".$value);
		}else{
			parent::setItem($key, $value);
		}
	}
	
	static function explodeString($string) {
		$arr = array();
		$lines = explode("\n", $string);
		foreach($lines as $line){
			$arr[] = (wordwrap($line, 50, "\n", true));
		}

		$result = join("\n", $arr);

		return $result;
	}

}
