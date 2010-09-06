<?php
namespace FW\Text;

class Xml {
	/**
	 * Производит проверку синтаксиса и валидацию xml по xml schema
	 * @throws EXml
	 * @param string текст xml
	 * @param string текст xml schema
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
	 * @return string список xml ошибок на текущий момент
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