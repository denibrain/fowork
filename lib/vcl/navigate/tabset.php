<?php
namespace FW\VCL\Navigate;

use \FW\App\App;
/**
 * Description of tabset
 *
 * @author d.russkih
 */
class Tabset extends \FW\VCL\Component{
	private $tabs;
	private $baseurl;
	private $selected;

	function getSelected(){ return $this->selected;}
	function setSelected($value){ $this->selected = $value;}
	function getBaseUrl(){ return $this->baseurl;}
	function setTabs($value) {
		$this->tabs = $value;
	}

	function __construct($name) {
		parent::__construct($name);
		$this->family = 'tabset';
		$this->selected = '';
		$this->tabs = array();
		$this->baseurl = implode('.', array_slice(App::$_->request->url->domain, 0, -1));
	}

	function display() {
		$skeleton = parent::display();
		$skeleton->add(D($this, 'baseurl,selected'));
		foreach($this->tabs as $key => $value)
			$skeleton->add(E('tab', A('id', $key, 'caption', $value)));
		return $skeleton;
	}
}
?>
