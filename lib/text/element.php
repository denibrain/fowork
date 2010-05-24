<?php
namespace FW\Text;

// TEXT and ORDERS INTO ASS!!!


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
		foreach($attr as $key=>$value) {
			$this->items[$key] = $value;
		}
	}
	
	private function addItem($e) {
		$this->last = $e;
		if (!isset($this->items[$e->tagName])) $this->items[$e->tagName] = new ElementGroup($e, $this);
		else $this->items[$e->tagName]->add($e);
		$e->rootNode = $e->rootNode;
	}

	private function addObjectAttrs($o) {
		foreach(get_object_vars($o) as $key=>$prop) {
			if (is_object($prop) || is_array($prop)) $this->addItem(E($key, $prop));
			else  $this->items[$key]= $prop;
		}
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
					return parent::__get($key);
		}
	}
	

	public function addItems($items) {
		if (!$items) return false;
		foreach($items as $arg) if ($arg) {
			if (is_string($arg)) {
				if ($this->tagName != 'item') {
					parse_str($arg, $varg);
					$this->addAttrs($varg);
				}
				else $this->tagName= $arg;
			}
			elseif (is_object($arg)) {
				if ($arg instanceof Element) $this->addItem($arg);
				else $this->addObjectAttrs($arg);
			}
			elseif (is_array($arg)) {
				if (is_string(key($arg)) && !is_object(current($arg))) $this->addAttrs($arg);
				else foreach($arg as $e) $this->addItem($e);
			}
		}
		return $this->last;
	}

	function add() {
		return $this->addItems(func_get_args());
	}

	function asXML() {
		$tag = "<{$this->tagName}";
		$body = '';
		foreach($this->items as $key=>$item)
			if ($item instanceof ElementGroup)
				$body .= $item->asXML();
			else
				$tag .= " $key='".T($item)->attr()."'";
				
		$tag .= $body?">$body</{$this->tagName}>":"/>";
		return $tag;
	}
	
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

class ElItem extends  \FW\Object {
	private $name;
	
	function __construct($name) {
		$this->name = $name;
	}
	
	function __get($key) {
		switch($key) {
			case 'name': return $this->name;
			case 'count': return 1;
			default: return parent::__get($key);
		}
	}
}

class ElementAttr extends ElItem {
	private $value;
	
	function __construct($name, $value) {
		parent::__construct($name);
		$this->value = $value;
	}

	function __get($key) {
		switch($key) {
			case 'text':
			case 'value':
				return $this->text;
			default: return parent::__get($key);
		}
	}
}

class ElementGroup extends ElItem {
	
	private $items;
	private $count;
	private $parentNode;
	
	
	function __construct($mixed, $parent) {
		parent::__construct(is_string($mixed)?$mixed:$mixed->tag);
		if (is_object($mixed)) $this->add($mixed);
		$this->parentNode = $parent;
		$this->count = 0;
	}

	function __get($key) {
		switch($key) {
			case 'count': return $this->count;
			default: return parent::__get($key);
		}
	}
	
	function asXML() {
		$text = '';
		foreach($this->items as $i) $text.= $i->asXML();
		return $text;
	}
	
	function add($e) {
		$this->items[] = $e;
		$e->parentNode = $this->parentNode;
		++$this->count;
	}
}
?>