<?php

class SiteInfo extends \FW\App\Module {

	function display($params) {
		return E('info', $this->dsField($params)->getA());
	}
}

?>