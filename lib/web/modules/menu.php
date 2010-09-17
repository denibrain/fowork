<?php
class Menu extends \FW\App\Module {

	function display($params) {
		return $this->dsList($params)->items(E('menu', A("selected", $this->app->request->url->address)));
	}
}
?>