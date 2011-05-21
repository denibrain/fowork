<?php
namespace FW\Exts\Nic;

/**
 * key-value map
 *
 * @author a.garipov, d.russkih
 */
class HashMap extends \FW\Object implements IteratorAggregate, ArrayAccess {
	public $params;
	private $type;
	private $isSimpleType;
	private static $simpleTypes = array(
		'boolean',
		'integer',
		'double',
		'string',
		'array',
		'resource',
		'NULL',
	);

	public function __construct($type = false) {
		if ($type) 
			$this->isSimpleType = in_array($type, self::$simpleTypes);

		$this->type = $type;
		$this->params = array();
	}
	
	function getIterator() { return new ArrayIterator($this->params); }
	function offsetExists($offset) { return isset($this->params[$offset]);}
	function offsetGet($offset) {return $this->params[$offset]; }
	function offsetSet($offset, $value) {$this->setItem($offset, $value);}
	function offsetUnset($offset) { unset($this->params[$offset]);}

	function setItem($key, $value) {
		if($this->type) {
			if(($this->isSimpleType && gettype($value) == $this->type) ||
				!$this->isSimpleType && !($value instanceof $this->type))
				throw new Exception('Invalid type of value of HashMap collection');
		}
		if ($value === null) {
			if (isset($this->params[$key])) unset($this->params[$key]);
		}
		elseif ($key === null)
			$this->params[] = $value;
		else
			$this->params[$key] = $value;
	}
	
	function getItem($key) {
		return isset($this->params[$key]) ? $this->params[$key] : null;
	}

	public function getArray() {return $this->params;}

	public function setArray($array) {
		foreach($array as $key => $value){
			$this->setItem($key, $value);
		}
	}
}