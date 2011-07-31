<?php
namespace FW\Text;

// TEXT and ORDERS INTO ASShole!!!

class Element extends \FW\Object {
	private $tagName = 'item';
	private $parentNode = NULL;
	private $rootNode = NULL;
	private $items = array();
	
	private $last;
	
	function __construct() {
		if (!$args = func_get_args()) return;
		$this->addItems($args);
		$this->rootNode = $this;
	}
	
	private function addAttrs($attr) {
		foreach($attr as $key => $value) {
			$this->items[$key] = $value;
		}
	}
	
	private function addItem($e) {
		$this->last = $e;
		if (!isset($this->items[$e->tagName]))
			$this->items[$e->tagName] = new ElementGroup($e, $this);
		else {
			if (!($this->items[$e->tagName] instanceof \FW\Text\ElementGroup)) {
				throw new \Exception("Conflict names $e->tagName");
			}
			$this->items[$e->tagName]->add($e);
		}
		$e->rootNode = $this->rootNode;
		return $e;
	}

	function __set($key, $value) {
		switch($key) {
			case 'items':
				$this->items = array();
				$this->addItems($value);
				break;
			case 'parentNode':
				$this->parentNode = $value;
				break;
			case 'tagName':
				if ($this->tagName !== $value) {
					$this->tagName = $value;
					if (isset($this->parentNode)) {
						$this->parentNode->delete($this);
						$this->parentNode->add($this);
					}
				}
			case 'rootNode':
				break;
			default:
				if (is_object($value)) $this->addItem($e);
				else $this->items[$key] = $value;
		}
	}
	
	function __get($key) {
		switch($key) {
			case 'items': return $this->items;
			case 'parentNode': return $this->parentNode;
			case 'tagName': return $this->tagName;
			case 'rootNode': return null;
			default:
				if (isset($this->items[$key]))
					return $this->items[$key];
				else
					return null;
		}
	}

	public function addItems($items) {
		$this->last = $this;
		if (!$items) return false;
		foreach($items as $arg) if ($arg) {
			if (is_string($arg)) {
				if (!$arg) continue;
				if ($arg[0]==='<')
					$this->loadFromXML($arg);
				else
				if ($arg[0]==='{' || $arg[0]==='[') {
					$arg = \json_decode ($arg);
					$this->last = $this->add($arg);
				}
				else
				if ($this->tagName != 'item') {
					parse_str($arg, $varg);
					$this->addAttrs($varg);
				}
				else
					$this->tagName= $arg;
			}
			elseif (is_scalar($arg)) {
				$this->item['value'] = $arg;
			}
			elseif (is_object($arg)) {
				if ($arg instanceof Element) $this->last = $this->addItem($arg);
				else $this->last = $this->embedObject($arg);
			}
			elseif (is_array($arg)) {
				foreach($arg as $key => $item) $this->last = $this->addNamedItem ($key, $item);
			}
		}
		return $this->last;
	}

	function add() {
		return $this->addItems(func_get_args());
	}

	function addNamedItem($key, $item) {
		$sk = is_string($key);
		if (is_scalar($item) ) {
			if ($sk) $this->items[$key] = $item;
			else return $this->add($item);
		}
		else
		if (is_object($item)) {
			if ($item instanceof Element) return $this->addItem($item);
			else {
				$e = $this->addItem(E($sk?$key:'item'));
				$e->embedObject($item);
				return $e;
			}
		}
		else
		if (is_array($item)) {
			$first = $this->addItem(E($sk?$key:'item'));
			$o = null;
			foreach($item as $subKey=>$subItem) {
				if (is_string($subKey)) $first->addNamedItem ($subKey, $subItem);
				else {
					if (!$o)
						$o = $first;
					else
						$o = $this->addItem(E($sk?$key:'item'));
					$o->add($subItem);
				}
			}
		}
		return $this;
	}

	function embedObject($obj) {
		foreach(get_object_vars($obj) as $key=>$prop) $this->addNamedItem ($key, $prop);
	}
	
	function asXML() {
		$tag = "<{$this->tagName}";
		$body = '';
		foreach($this->items as $key=>$item)
			if ($item instanceof ElementGroup)
				$body .= $item->asXML();
			else
				$tag .= " $key='".T((string)$item)->attr()."'";
				
		$tag .= $body?">$body</{$this->tagName}>":"/>";
		return $tag;
	}

	function loadFromXML($xml) {
		if (substr($xml, 0, 2)!='<?') $xml = '<?xml version="1.0" encoding="'.FW_CHARSET.'" ?>'.$xml;
		$xml = \simplexml_load_string($xml);
		$this->tagName = $xml->getName();
		$this->importNode(get_object_vars($xml));
		return $this;
	}

	private function importNode($node) {
		if (isset($node['@attributes'])) {
            foreach($node['@attributes'] as &$n) $n = iconv('utf-8', FW_CHARSET, $n);
			$this->add($node['@attributes']);
			unset($node['@attributes']);
		}
		foreach($node as $tag => $body) {
			if (!is_array($body)) $body = array($body);
			foreach($body as $item)
				$this->add(E($tag))->importNode(\get_object_vars ($item));
		}
	}

	/** JSON **/
	function aItem(&$item, $key) {
		if ($item instanceof ElementGroup) {
			$els = array();
			foreach($item as $el) {
				$els[] = $this->toArray($el);
			}
			$item = $els;
		}
		else $item = iconv(FW_CHARSET, 'utf-8',$item);
	}

	function toArray($root) {
		$loc = $root->items;
		array_walk($loc, array($this, 'aItem'));
		return $loc;
	}
	
	function asJSON() {
		return json_encode($this->toArray($this));
	}

	/**
	 *
	 * @param array $lst
	 * @param string $itemName
	 * @param string $groupName
	 * @return Element
	 */
	static function lst($lst, $itemName = 'item', $groupName = 'list') {
		$e = is_string($groupName) ? new Element($groupName) : $groupName;
		foreach($lst as $id) $e->add(new Element($itemName, array('id' => $id)));
		return $e;
	}
	static function dic($dic, $itemName = 'item', $groupName = 'dic') {
		$e = is_string($groupName) ? new Element($groupName) : $groupName;
		foreach($dic as $id => $name) $e->add(new Element($itemName, array('id'=>$id, 'name'=>$name)));
		return $e;
	}	
}