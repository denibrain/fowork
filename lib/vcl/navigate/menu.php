<?php
namespace FW\VCL\Navigate;

use \FW\App\App;
/**
 * Description of tabset
 *
 * @author d.russkih
 */
class Menu extends \FW\VCL\Component{
	private $selected;

	function getSelected(){ return $this->selected;}
	function setSelected($value){ $this->selected = $value;}

	function __construct($name) {
		parent::__construct($name);
		$this->family = 'menu';
		$this->selected = '';
	}

	function display() {
		$skeleton = parent::display();
		$skeleton->add(D($this, 'selected'));
		return $skeleton;
	}
}
?>
