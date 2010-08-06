<?php
namespace FW\VCL;

/**
 * Description of data
 *
 * @author d.russkih
 */
class DataHolder extends Component {

	private $data;

	function __construct($name) {
		parent::__construct($name);
		$this->family = 'dataholder';
		$this->data = \E('data');
	}

	function customDisplay($skeleton) {
		$skeleton->add($this->data);
	}

	function getData() {return $this->data;}
}
?>
