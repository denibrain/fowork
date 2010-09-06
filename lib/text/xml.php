<?php
namespace FW\Text;

class Xml {
	/**
	 * ���������� �������� ���������� � ��������� xml �� xml schema
	 * @throws EXml
	 * @param string ����� xml
	 * @param string ����� xml schema
	 */
	public static function validateXml($xmlString, $xmlSchemaString) {
		$dom = new \DOMDocument();
		if(!$isWellformed = $dom->loadXML($xmlString)){
			throw new EXml("XML syntax errors:\n" .self::getXmlErrors(), EXml::xmlSyntaxError);
		}
		if(!$isValid = $dom->schemaValidateSource($xmlSchemaString)){
			throw new EXml("XML validate errors:\n" . self::getXmlErrors(), EXml::xmlValidateError);
		}
	}
		
	/**
	 * @return string ������ xml ������ �� ������� ������
	 */
	public static function getXmlErrors() {
		$errorString = '';
		foreach(libxml_get_errors() as $error) {
			$errorString.=('line ' . $error->line . ': ' . $error->message );
		}
		return $errorString;
	}
}

class EXml extends \Exception {
	const xmlSyntaxError = 100;
	const xmlValidateError = 101;
}