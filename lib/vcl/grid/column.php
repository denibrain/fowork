<?php
namespace FW\VCL\Grid;

class Column extends \FW\Object {
	private $name;
	private $width;
	private $sort;
	private $caption;
	private $align;

	const LEFT = 'left';
	const JUSTIFY = 'justify';
	const RIGHT = 'right';
	const CENTER = 'center';

	const NUMBER = '#';
	const TOOL = '@';

	const ANY = '*';

	const SORTNONE = 0;
	const SORTASC = 1;
	const SORTDESC = 2;

	function __construct($name, $caption = '', 
		$width = Column::ANY, $align = Column::LEFT, $sort = Column::SORTNONE) {
		$this->name = $name;		
		$this->width = $width;		
		$this->caption = $caption;		
		$this->align = $align;		
		$this->sort = $sort;		
	}

	function display() {
		return E('col', A(
			'name', $this->name,
			'width', $this->width,
			'caption',$this->caption,
			'align', $this->align,
			'sort', $this->sort
		));

	}

	function getName() { return $this->name; }

}