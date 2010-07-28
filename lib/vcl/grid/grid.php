<?php
namespace FW\VCL\Grid;
      
class Grid extends \FW\VCL\Component {

	public $dataSource;
	public $emptyMessage;

	function __construct($name) {
		parent::__construct($name);
		$this->family = 'grid';

	}
	function display() {
		$skeleton = parent::display();
		$skeleton->add(A('empty-message', $this->emptyMessage));
		foreach($this->dataSource as $rdata) {
			$row = $skeleton->add(E('row', $rdata));
			foreach($this->cols as $col) {
				$n = $col->name;
				$row->add(E('cell', A('id', $n, 
					'value', isset($rdata[$n])?$rdata[$n]:'')));
			}
		}
		return $grid;
	}
}

?>