<?php

class Search extends \FW\App\Module
{

	function displayBox() {
		$s = isset($_SESSION['search_query']['query']) ? $_SESSION['search_query']['query'] : '';
		return E('form', A('query', $s));
	}

	function display() {
		if (empty($_GET['search'])) return E('form');

		$params = array(
			'query' => $_GET['search'],
			'pageno' => 0,
			'pagesize' => 50,
		);

		$el = E('page', array(
		    E('form', $params),
		    $this->dsQuery($params)->items('results', 'result'),
		));

		return $el;
	}
}