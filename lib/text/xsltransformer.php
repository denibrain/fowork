<?php
namespace FW\Text;

class XSLTransformer extends \FW\Object {
	private $xslPath;
	private $xslh;
	
	function __construct($path) {
		$this->xslPath = $path;
		$this->xslh = new \xsltprocessor();
	}
	
	function __destruct() {
	}
	
	public function __invoke($xml) {
		return $this->transform($xml);
	}
	
	public function transform($xmlraw, $xslName = false) {
		if (!$xmlraw) return false;
		$xml=new \DOMDocument();
		if (!$xml->loadXML('<?xml version="1.0" encoding="'.FW_CHARSET.'"?>
			<!DOCTYPE local [
				<!ENTITY nbsp  "&#xA0;">
				<!ENTITY raquo  "&#xBB;">
				<!ENTITY laquo  "&#xAB;">
				<!ENTITY mdash  "&#x2014;">
				
			]>'.$xmlraw)) {
			echo \htmlentities($xmlraw);
			throw new \Exception("Invalid data for $transform");
		};
		
		if ($xslName) $this->template = $xslName;
		return (string)$this->xslh->transformToXML($xml);
	}
	
	function __set($key, $value) {
		switch ($key) {
			case 'template':
				$xsl=new \DOMDocument();
				$f = $this->xslPath."$value.xsl";
				if (!file_exists($f)) {
					throw new \Exception("{$this->xslPath}$value.xsl not found");
				}
				$xsl->load($f);

				$this->xslh->importStyleSheet($xsl);
				break;
			default:
				parent::__set($key, $value);
		}
	}
	
}

?>