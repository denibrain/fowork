<?php
namespace FW\VCL\Forms;

class Group extends FormElement {
	
	private $name;
	private $caption;
	private $items = array();
	
	function __construct($caption, $name = '') {
		$this->name = $name;
		$this->caption = $caption;
	}
	
	function add($el) {
		$el->form = $this->form;
		if ($el instanceof Field) $this->form->fields->add($el);
		$this->items[] = $el;
	}
	
	function display() {
		$e = E('group', A('caption', $this->caption, 'name', $this->name));
		foreach($this->items as $item) $e->add($item->display());
		return $e;
	}
}

