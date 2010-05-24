<?php
namespace FW\VCL;

class Filter extends Component {
	
	public $value;
	
	function __construct($name) {
		if (isset($_GET['filter'])) $this->params['filter'] = $_GET['filter'];
	}
	
	function 
}

?>