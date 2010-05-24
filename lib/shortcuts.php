<?php
function E() {
	$e = new \FW\Text\Element();
	$e->addItems(func_get_args());
	return $e;
}

function T($text) {
	return new \FW\Text\Text($text);
}

function A() {
	$result = array();
	$args = func_get_args();
	while (list(,$e) = each($args)) list(, $result[$e]) = each($args);
	return $result;
}

function he($a) { return T($a)->html; }
function remle($a) { return T($a)->remEOL(); }
function fixle($a) { return T($a)->setEOL(); }

?>