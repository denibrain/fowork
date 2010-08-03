<?php
namespace FW\VCL\Navigate;

/**
 * Description of menuitem
 *
 * @author d.russkih
 */
class Menuitem extends \FW\VCL\Component {
	private $link;
	private $caption;

    function __construct($name, $link = '', $caption = '') {
		parent::__construct($name);
		$this->family = 'menuitem';
		$this->link = str_replace('@', \FW\App\App::$_->request->url, $link);
		$this->caption = $caption ? $caption : $link;
	}

	function customDisplay($skeleton) {
		$skeleton->add(D($this, "caption,link"));
	}

	function getLink() { return $this->link; }
	function getCaption() { return $this->caption; }
}
?>
