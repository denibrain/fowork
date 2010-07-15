<?php
namespace FW\VCL\Grid;
      
class Grid extends \FW\VCL\Component {

	public $cols;
	public $dataSource;
	public $emptyMessage;

	function __construct($name, $init = '') {
		parent::__construct($name);
		$this->cols = array();
		if ($init) include $init;
	}

	function display() {
		$grid = E('grid', A('name', $this->name, 
			'empty-message', $this->emptyMessage));
		foreach($this->cols as $col) $grid->add($col->display());
		foreach($this->dataSource as $rdata) {
			$row = $grid->add(E('row', $rdata));
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