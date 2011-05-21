<?php
namespace FW\Text;

/*
 * @property latin - latinized text
 *
 */
class Text extends \FW\Object {

	public $text;
	public $charset;
	
	function __construct($text, $cs = FW_CHARSET) {
		$this->text = $text;
		$this->charset = $cs;
	}
	
	function __toString() { return $this->text; }

	function __get($key) {
		switch($key) {
			case 'latin': return $this->latin();
			case 'qencoded': return $this->qencoded();
			case 'html': return $this->html();
			case 'squoted': return str_replace(
				array('\\', "'"), array('\\\\', "\\'"), $this->text);
			case 'dquoted': return str_replace(
				array('\\', '"'), array('\\\\', '\\"'), $this->text); 
			default:
				return parent::__get($key);
		}
	}

	private function latin() {
		$table = array(
			'à'=>'a', 'á'=>'b', 'â'=>'v', 'ã'=>'g', 'ä'=>'d', 'å'=>'e',	'¸'=>'yo','æ'=>'zh','ç'=>'z', 'è'=>'i',	'é'=>'y',
			'ê'=>'k', 'ë'=>'l', 'ì'=>'m', 'í'=>'n', 'î'=>'o', 'ï'=>'p', 'ð'=>'r', 'ñ'=>'s', 'ò'=>'t', 'ó'=>'u', 'ô'=>'f',
			'õ'=>'kh', 'ö'=>'c', '÷'=>'ch','ø'=>'sh','ù'=>'sch','ú'=>'y', 'ü'=>'', 'û'=>'yi','ý'=>'e', 'þ'=>'yu','ÿ'=>'ya',
	
			'À'=>'A', 'Á'=>'B', 'Â'=>'V', 'Ã'=>'G', 'Ä'=>'D', 'Å'=>'E', '¨'=>'Yo','Æ'=>'Zh','Ç'=>'Z', 'È'=>'I', 'É'=>'Y',
			'Ê'=>'K', 'Ë'=>'L', 'Ì'=>'M', 'Í'=>'N', 'Î'=>'O', 'Ï'=>'P', 'Ð'=>'R', 'Ñ'=>'S', 'Ò'=>'T', 'Ó'=>'U', 'Ô'=>'F',
			'Õ'=>'KH', 'Ö'=>'C', '×'=>'Ch','Ø'=>'Sh','Ù'=>'Sch','Ú'=>'Y', 'Ü'=>'', 'Û'=>'Yi','Ý'=>'E', 'Þ'=>'Yu','ß'=>'Ya'
		);
		return strtr($this->text, $table);
	}
	
	private function qencoded() {
		///[^A-Za-z0-9!#$%&\'*+\/=?^_`{|}~-]/
		if (!preg_match('/[^@+,;:<>A-Za-z0-9!#$%&\'*+\/=?^_`{|}~. -]/', $this->text))
			return $this->text;
		if (FW_CHARSET!='utf-8') {
			$preferences = array(
				"input-charset" => $this->charset,
				"output-charset" => $this->charset,
				"line-length" => 76,
				"line-break-chars" => PHP_EOL,
				"scheme"=> 'Q'
			);
			return substr(iconv_mime_encode('', $this->text, $preferences), 2);
		} else {
			return \mb_encode_mimeheader($this->text, 'utf-8', 'Q', PHP_EOL);
		}
	}

	function attr()  {
		$this->setEOL("\x0A");
		if (!is_string($this->text)) {
			print_r($this->text);
			throw new \Exception("Is not text: ".$this->text);
		}
		return strtr($this->text,
			array("\x0A"=> "&#10;", "'"=>"&#39;", "&"=>"&amp;", ">"=>"&gt;", "<"=>"&lt;", '"'=>"&quot;"));
	}

	function html()  {
		return strtr($this->text,
			array("'"=>"&#39;", "&"=>"&amp;", ">"=>"&gt;", "<"=>"&lt;", '"'=>"&quot;"));
	}
	
	function remEOL() { return $this->text = str_replace(array("\x0D", "\x0A"), "", $this->text); }
	function setEOL($le = PHP_EOL) { return $this->text = strtr($this->text, array("\x0D"=>'', "\x0A"=> $le)); }
	
	function toCharset($cs) {
		$this->text = iconv($this->charset, $cs, $str);
		$this->charset = $cs;
		return $this->text;
	}

}