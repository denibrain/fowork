<?php
namespace FW\Text;

class EParser extends \Exception {
	function __construct($message) {parent::__construct($message);}
}

class Parser extends \FW\Object {
	private $mask;
	private $map;
	private $masks;
	private $terms;
	private $maps;
	private $state;
	private $newState;
	private $mapName;
	private $stack = array();
	private $callback;
	private $text;
	
	
	private $ignoreSpace = false;
	
	function __construct($stx) {
		// loading syntax
		require $stx;

		$cvt = function(&$value, $key) {
			$value = "(?<$key>$value)";
		};

		// expand complex translations
		$newmaps = array();
		foreach($this->maps as $mapName => $states) {
			$newmaps[$mapName] = array();
			foreach($states as $state=>$trans) {
				$newmaps[$mapName][$state] = array();
				foreach($trans as $term => $newstate) {
					if (false===strpos($term, ','))
						$newmaps[$mapName][$state][$term] = $newstate;
					else {
						$termNames = preg_split('/\s*,\s*/', $term);
						foreach($termNames as $term) 
							$newmaps[$mapName][$state][$term] = $newstate;
					}
				}
			}
		}
		$this->maps = $newmaps;
		
		// separete terms
		if (!is_array(current($this->terms))) {
			if (!is_array($this->terms)) throw new \Exception("Terms not set");
			$globalTerms = $this->terms;
			array_walk($globalTerms, $cvt);
			$this->masks = array();

			foreach($this->maps as $mapName => $states) 
				$this->masks[$mapName] = array();
			
			foreach($globalTerms as $term => $expr) {
				foreach($this->maps as $mapName => $states)
				if ($term == 'space') $this->masks[$mapName][$term] = $expr;
				else {
					foreach($states as $state => $trans) {
						if (isset($trans[$term])) {
							$this->masks[$mapName][$term] = $expr;
							break;
						}
					}
				}
			}
			
			array_walk($this->masks, function(&$value) {$value = '/'.implode("|", $value).'/ix';} );
		} else {
			foreach($this->terms as $name => $termSet) {
				array_walk($termSet, $cvt);
				$this->masks[$name] =  '/'.implode("|", $termSet).'/ix';
			}
		}
	}
	function compile($text, $callback) {
		$this->text = $text;
		$this->callback = $callback;
		$this->stack = array(); # stack of states

		reset($this->maps);
		$this->map = current($this->maps);
		$this->mapName = key($this->maps);
		$this->state = key($this->map);
		$this->mask = $this->masks[$this->mapName];
		$textPos = 0;
		$len = strlen($text);
		
		while ($textPos < $len) {
			if (!preg_match($this->mask, $text, $matches, PREG_OFFSET_CAPTURE, $textPos)) {
				$this->proceed('raw', substr($text, $textPos), $textPos);
				$textPos = $len;
			} else {
				list($term, $newPos) = array_shift($matches);
				$termName = key(array_filter($matches, function($m) {return $m[1]!=-1;}));
				
				if ($newPos > $textPos) {
					$this->proceed('raw', substr($text, $textPos, $newPos - $textPos), $textPos);
				}
				$this->proceed($termName, $term, $newPos);
				$textPos = $newPos += strlen($term);
			}
		}
		$this->proceed('end', '', $len);
	}
	
	function proceed($type, $val, $pos) {
		if ($type == 'space' && $this->ignoreSpace) return;
		
		if (!isset($this->map[$this->state][$type])) {
			print_r($this->map[$this->state]);
			echo $this->mask;
			throw new EParser("Unexpected term '$type' in state {$this->state}@{$this->mapName} [pos: $pos]:\n".
				substr_replace($this->text, '^', $pos, 0));
		}

		$this->newState = $this->map[$this->state][$type];

		call_user_func($this->callback, $type, $val, $pos, $this);

		if ($this->newState[0] == '+') {
			list($st, $newmapName) = explode(',', substr($this->newState, 1));
			$st = trim($st);
			array_push($this->stack, array($this->mapName, $st, $pos));
			$this->mapName = trim($newmapName);
			if (!isset($this->maps[$this->mapName]))
				throw new \Exception("Map [$this->mapName] not found");
			$this->map = $this->maps[$this->mapName];
			$this->mask = $this->masks[$this->mapName];
			$this->state = key($this->map);
		}
		else
		if ($this->newState == '-') {
			if (!$this->stack) throw new \Exception('Invalid pop stack');
			$childMap = $this->mapName;
			list($this->mapName, $this->state, $oldPos) = array_pop($this->stack);
			$this->map = $this->maps[$this->mapName];
			$this->mask = $this->masks[$this->mapName];

			call_user_func($this->callback, "$childMap.end", '', $oldPos, $this);
		}
		else $this->state = $this->newState;
		
	}
	
	public function __get($key) {
		switch($key) {
			case 'state': return $this->state;
			case 'mapName': return $this->mapName;
			case 'newState': return $this->newState;
			default: return parent::__get($key);
		}
	}
}

?>