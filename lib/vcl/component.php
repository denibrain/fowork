<?php
namespace FW\VCL;

class Component extends \FW\Object {
	public $name;
	public $params;
	private $controls;

	private $className;
	protected $family;
	public $id;
	private $owner;

	/**
	 * Contructor
	 * @param string $name Name of control
	 */
	function __construct($name) {
		$this->family = 'component';
		$this->name = $name;
		$this->owner = null;
		$this->controls = new Controls();

		$this->className = strtolower(substr(get_class($this)));

		$this->id = "{$this->className}_$this->name}";
		if (isset($_SESSION[$this->fullname])) $this->params = $_SESSION[$this->fullname];
		else $this->params = array();
	}
	
	function display() {
		$skeleton = E($this->family, D($this, 'id,name'), A('class', $this->className));
		foreach($this->controls as $item)
			$skeleton->add($item->display());
		return $skeleton;
	}

	/**
	 * Add control & set owner
	 * @param \FW\VCL\Component $control Some control to add
	 */
	function add($control) {
		$this->controls->add($control);
		$control->owner = $this;
	}

	function __destruct() {
		$_SESSION[$this->fullname] = $this->params;
	}

	function getId() { return (isset($this->owner) ? $this->owner->id.'.' : '' ).$this->id; }
	function getName() {return $this->name;}
	function getControls() {return $this->controls;}
	function getClassName() {return $this->className;}
	function getOwner() {return $this->owner;}
	function setOwner($value) {$this->owner = $value;}
}
