<?php
namespace FW\VCL;

class Component extends \FW\Object {
	private $name;
	private $controls;
	private $className;
	protected $family;
	private $visible;
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
		$this->visible = true;

		$this->className = strtolower(get_class($this));
		$pos = \strrpos($this->className, '\\');
		if ($pos !== false) $this->className = substr($this->className, $pos + 1);
	}

	function display() {
		$skeleton = E($this->family, D($this, 'id,name,visible'), A('class', $this->className));
		foreach($this->controls as $item)
			$skeleton->add($item->display());
		
		$this->customDisplay($skeleton);

		return $skeleton;
	}

	function customDisplay($skeleton) {

	}

	function hideInsiders() {
		foreach($this->controls as $item) $item->hide();
	}

	function hide() {
		$this->visible = false;
	}

	/**
	 * Add control & set owner
	 * @param \FW\VCL\Component $control Some control to add
	 */
	function add($control) {
		$control->owner = $this;
		$this->controls->add($control);
		return $control;
	}

	function remove($control) {
		if (\is_string($control)) $control = $this->controls->$control;
		if ($control) {
			$this->controls->remove($control);
			$control->owner = null;
		}
		return $control;
	}

	/**
	 * Perform a event for object with ID = sender
	 * @param string $sender
	 * @param string $event
	 * @param array $data
	 * @return void
	 */
	function perform($sender, $event, $data) {
		if ($sender === $this->id) {
			$this->handleEvent($event, $data);
		}
		else {
			/* @var $control FW\VCL\Component */
			$control = $this->controls->getById($sender);
			if ($control) {
				$control->perform($sender, $event, $data);
			}
		}
	}

	function handleEvent($event, $data) {
		$handler = 'do'.$event;
		if (\method_exists($this, $handler))
			$this->$handler($data);
	}

	function getId() { return (isset($this->owner) ? $this->owner->id.'.' : '' ).$this->name; }
	function getName() {return $this->name;}
	function getVisible() {return $this->visible;}
	function getControls() {return $this->controls;}
	function getClassName() {return $this->className;}
	function getOwner() {return $this->owner;}
	function setOwner($value) {	$this->owner = $value; }
}
