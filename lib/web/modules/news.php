<?php
class News extends \FW\App\Module {

	private $item;
	
	/* @param limit=20;
	*/
	function display($params) {
		return $this->buildXML($this->dsLast($params));
	}
	
	/* @param year=20;
	   @param limit=20;
	*/
	function displayYear($params) {
		return $this->buildXML($this->dsY($params), $params);
	}

	function mapYear($params) {
		return $this->dsYear($params)->dic();
	}

	function buildXML($ds, $p = array()) {
		return $ds->items(E('news', $p));
	}
	
	function captionItem($params) {
		if (!($this->item = $this->dsItem($params)->getA())) throw new E404();
		return $this->item['name'];
	}

	function displayItem($params) {
		if (!$this->item) $this->captionItem($params);
		return E($this->item);
	}
	
}
?>