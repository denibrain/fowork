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
			'�'=>'a', '�'=>'b', '�'=>'v', '�'=>'g', '�'=>'d', '�'=>'e',	'�'=>'yo','�'=>'zh','�'=>'z', '�'=>'i',	'�'=>'y',
			'�'=>'k', '�'=>'l', '�'=>'m', '�'=>'n', '�'=>'o', '�'=>'p', '�'=>'r', '�'=>'s', '�'=>'t', '�'=>'u', '�'=>'f',
			'�'=>'kh', '�'=>'c', '�'=>'ch','�'=>'sh','�'=>'sch','�'=>'y', '�'=>'', '�'=>'yi','�'=>'e', '�'=>'yu','�'=>'ya',
	
			'�'=>'A', '�'=>'B', '�'=>'V', '�'=>'G', '�'=>'D', '�'=>'E', '�'=>'Yo','�'=>'Zh','�'=>'Z', '�'=>'I', '�'=>'Y',
			'�'=>'K', '�'=>'L', '�'=>'M', '�'=>'N', '�'=>'O', '�'=>'P', '�'=>'R', '�'=>'S', '�'=>'T', '�'=>'U', '�'=>'F',
			'�'=>'KH', '�'=>'C', '�'=>'Ch','�'=>'Sh','�'=>'Sch','�'=>'Y', '�'=>'', '�'=>'Yi','�'=>'E', '�'=>'Yu','�'=>'Ya'
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