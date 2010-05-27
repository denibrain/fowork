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
			'а'=>'a', 'б'=>'b', 'в'=>'v', 'г'=>'g', 'д'=>'d', 'е'=>'e',	'ё'=>'yo','ж'=>'zh','з'=>'z', 'и'=>'i',	'й'=>'y',
			'к'=>'k', 'л'=>'l', 'м'=>'m', 'н'=>'n', 'о'=>'o', 'п'=>'p', 'р'=>'r', 'с'=>'s', 'т'=>'t', 'у'=>'u', 'ф'=>'f',
			'х'=>'kh', 'ц'=>'c', 'ч'=>'ch','ш'=>'sh','щ'=>'sch','ъ'=>'y', 'ь'=>'', 'ы'=>'yi','э'=>'e', 'ю'=>'yu','я'=>'ya',
	
			'А'=>'A', 'Б'=>'B', 'В'=>'V', 'Г'=>'G', 'Д'=>'D', 'Е'=>'E', 'Ё'=>'Yo','Ж'=>'Zh','З'=>'Z', 'И'=>'I', 'Й'=>'Y',
			'К'=>'K', 'Л'=>'L', 'М'=>'M', 'Н'=>'N', 'О'=>'O', 'П'=>'P', 'Р'=>'R', 'С'=>'S', 'Т'=>'T', 'У'=>'U', 'Ф'=>'F',
			'Х'=>'KH', 'Ц'=>'C', 'Ч'=>'Ch','Ш'=>'Sh','Щ'=>'Sch','Ъ'=>'Y', 'Ь'=>'', 'Ы'=>'Yi','Э'=>'E', 'Ю'=>'Yu','Я'=>'Ya'
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
			return mb_encode_mimeheader($this->text, 'utf-8', 'Q', PHP_EOL);
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
	function setEOL($le = PHP_EOL) { return $this->text = str_replace(array("\x0A\x0D", "\x0D\x0A", "\x0A", "\x0D"), $le, $this->text); }
	
	function toCharset($cs) {
		$this->text = iconv($this->charset, $cs, $str);
		$this->charset = $cs;
		return $this->text;
	}

}

function int2s($i, $valute=0) {
	$valutes = array(array('', '', ''), array('рубль','рубля','рублей'), array('доллар','доллара','долларов'));
	$z = array(0=>$valutes[$valute], 1 => array('тысяча','тысячи','тысяч'), 2 => array('миллион','миллиона','миллионов'), 3 => array('миллиард','миллиарда','миллиардов'));
	if (!$i) return trim('ноль '.$z[0][2]);
	list($i, $frac) = explode('.', $i);
	$i = str_split(strrev((string)((int)$i)), 3);
	$hl = array(1=>'сто', 'двести', 'триста', 'четыреста', 'пятьсот', 'шестьсот', 'семьсот', 'восемьсот','девятьсот');
	$e1 = array('десять', 'одинадцать', 'двенадцать', 'тринадцать', 'четырнадцать', 'пятьнадцать', 'шестнадцать', 'семьнадцать', 'восемьнадцать', 'девятьнадцать');
	$el = array('', 'один', 'два', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять');
	$e2 = array('', 'одна', 'две', 'три', 'четыре', 'пять', 'шесть', 'семь', 'восемь', 'девять');
	$dl = array(2=>'двадцать', 'тридцать', 'сорок', 'пятьдесят', 'шестьдесят', 'семьдесят', 'восемьдесят', 'девяносто');
	foreach($i as $k => $th) if ((int)$th) {
		$digs = str_split($th);
		$e = $digs[0];
		$d = isset($digs[1])?$digs[1]:0;
		$h = isset($digs[2])?$digs[2]:0;
		$i[$k] = trim(
			($h?$hl[$h].' ':'').
			($d > 1?$dl[$d].' ':'').
			($d==1?$e1[$e].' ':($e?($k==1?$e2[$e]:$el[$e]).' ':'')) . $z[$k][$d==1||$e==0||$e>4?2:($e==1?0:1)]);
	} else $i[$k] = '';
	if ($frac) {
		$fr = array(array('', '', ''), array('копейка','копейки','копеек'), array('цент','цента','центов'));
		array_unshift($i, sprintf('%02', $frac).' '.$fr[$k] [ ($e=$frac % 10)>4||$e==0||($frac>10&&$frac<20) ? 2: ($e==1?0:1) ]);
	}
	return trim(ucfirst(implode(' ', array_reverse($i))));
}
?>