<?php
namespace FW;

function data2xml($a, $tag = '') { $xml = ''; foreach($a as $key=>$value) $xml .= "<$key>$value</$key>"; if ($tag) return "<$tag>$xml</$tag>"; else return $xml; }
function arr2xml($a, $name='item') { $xml=''; foreach($a as $key=>$value) $xml .= "<$name id='$key'>$value</$name>"; return $xml;}
function prop2xml($a, $tag='item', $content = '') {
	$xml = "<$tag"; foreach($a as $key=>$value) $xml .= " $key=\"".he($value).'"';
	return $content ? $xml.">\n$content\n</$tag>" : $xml." />";
}
function e($tagName, $xml = '', $attrs = array()) { return prop2xml($attrs, $tagName, $xml); }


class XSLProcessor exnteds Object {

	private $xslPath = '';

	function __construct($xslPath) {
		$this->handle = new xsltprocessor();
	}

	function transform($xml_data, $transform) {
		if (!$xml_data) return false;
		$xml=new DOMDocument();
		if (!$xml->loadXML('<?xml version="1.0" encoding="windows-1251"?>
			<!DOCTYPE local [
				<!ENTITY nbsp  "&#xA0;">
				<!ENTITY raquo  "&#xBB;">
				<!ENTITY laquo  "&#xAB;">
				<!ENTITY mdash  "&#x2014;">
			]>'.$xml_data)) {
			throw new Exception("Invalid data >$xml_data< for $transform");
		};
		$xsl=new DOMDocument();
		$f = $this->design."xsl/$transform.xsl";
		if (!file_exists($f)) {
			throw new Exception("$transform not found");
		}
		$xsl->load($f);

		$this->xslh->importStyleSheet($xsl);
		return $this->xslh->transformToXML($xml);
	}
}

?>