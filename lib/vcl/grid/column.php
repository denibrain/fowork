<?php
namespace FW\VCL\Grid;

class Column extends \FW\VCL\Component {
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

	function __construct($name, $caption = '', $width = Column::ANY, 
			$align = Column::LEFT, $sort = Column::SORTNONE) {
		parent::__construct($name);

		$this->family = 'col';
		$this->caption = $caption;
		$this->width = $width;
		$this->align = $align;
		$this->sort = $sort;
	}

	function display() {
		$skeleton = parent::display();
		$skeleton->add(D($this, 'width,caption,align,sort'));
		return $skeleton;
	}
}