<?php
namespace FW\VCL\Grid;
      
class Grid extends \FW\VCL\Component {

	public $dataSource;
	public $emptyMessage;
	public $filter = null;
	public $sort = null;

	function __construct($name) {
		parent::__construct($name);
		$this->family = 'grid';

	}
	function display() {
		$skeleton = parent::display();
		$skeleton->add(A('sort', $this->sort, 'empty-message', $this->emptyMessage));
		if (isset($this->filter))
			$this->dataSource->filter = $this->filter->filter;

		if (isset($this->sort))
			$this->dataSource->sort = $this->sort;

		$cols = $this->controls->setOf('\FW\VCL\Grid\Column');
		foreach($this->dataSource as $rdata) {
			$row = $skeleton->add(E('row', $rdata));
			foreach($cols as $col) {
				$n = $col->name;
				$row->add(E('cell', A('id', $n, 
					'value', isset($rdata[$n])?$rdata[$n]:'')));
			}
		}
		return $skeleton;
	}

	function doSort($data) {
		if (isset($data['sort']))
			$this->sort = $data['sort'];
	}

}
