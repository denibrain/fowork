<?php
namespace FW\VCL;
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

class Page extends Component {

	private $caption;

	function __construct($name) {
		parent::__construct($name);
		$this->family = 'page';

	}

	function getCaption() { return $this->caption; }

	function handleEvent($event, $data) {}
}
