<?php
/* Based on FPDF Version: 1.6/2008-08-03/Olivier PLATHEY */

define('MMPPT', 72/25.4);
define('CMPPT', 72/2.54);
define('RE_SIZE', '/^([+-]?[0-9]+(?:\.[0-9]+)?)(cm|pt|mm|in)?/');

define('FS_NONE', 	   0x00);
define('FS_ITALIC',    0x01);
define('FS_BOLD',	   0x02);
define('FS_UNDERLINE', 0x04);

define('BR_NONE',   0);
define('BR_SIGNLE', 1);
define('BR_DOUBLE', 2);

class PDFObject {
	private $content = '';
	private $no = 0;
	private $gen = 0;
	function __construct() {
		$this->no = $no;
		$this->gen = $gen;
	}
	
	function __get($key) {
		switch($key) {
			case 'link' : return '$this->no $this->gen R';
		}
	}
	function __toString() {
		return "$this->no $this->gen obj\n$this->content\nendobj\n";
	}
}

class PDFString extends PDFObject {
	private $value = '';
	
	function __construct($string) {
		$this->value = $this->string;
	}
	
	function __toString() {
		return '('.str_replace(array('\\', '(', ')', "\r"),	array('\\\\','\\(', '\\)', '\\r'), $this->value).')';

	}
}

class PDFDict extends PDFObject {
	private $items = array();
	function __toString() {
		$t = "<<\n";
		foreach ($items as $key => $object) $t.="/$key ".$object;
		return $t.=">>\n";
	}
}

class PDFStream extends PDFObject {
	private $dict;
	private $data;
	
	function __construct($data) {
		$this->data = $data;
		$this->dict = new PDFDict(Length, strlen($this->data))
	}
	
	function __toString() {
		return "{$this->dict}stream\n{$this->data}\nendstream\n";
	}
}

class PDFDocument {
	static private $envChecked = false;
	static public $fontPath = '';
	static private $layouts = array(
		'single' => '/SinglePage',
		'continuous' => '/OneColumn',
		'two' => '/TwoColumnLeft'
	);
	
	static private $zooms = array(
		'fullpage' => '/Fit',
		'fullwidth' => '/FitH null',
		'real' => '/XYZ null null 1'
	);

	static public $units = array('pt'=>1, 'mm'=>MMPI, 'cm'=>CMPPT, 'in'=>72);
	
	static public $pageFormats = array(
		'a3' => array(841.89,1190.55),
		'a4' => array(595.28,841.89),
		'a5' => array(420.94,595.28),
		'letter'=>array(612,792),
		'legal' =>array(612,1008));
	
	static function envCheck() {
		//Check availability of %F
		if(sprintf('%.1F',1.0)!='1.0') throw new EApp('This version of PHP is not supported');
		//Check mbstring overloading
		if(ini_get('mbstring.func_overload') & 2) throw new EApp('mbstring overloading must be disabled');
		//Disable runtime magic quotes
		if(get_magic_quotes_runtime()) @set_magic_quotes_runtime(0);
		self::$envChecked = true;
	}

	private $objects = array();
	private $catalog;
	private $info;
	private $autoPageBreak = true;
	
	public function __construct($size = 'A4', $portrate = true) {
		if (!self::$envChecked) self::envCheck();
		$this->addObject(new PDFEmpty);
		$this->addObject($this->pages = new PDFDict(
			'Type', '/Pages',
			'Kids', new PDFArray(),
			'Count', 0,
			'MediaBox', new PDFArray(0, 0, self::$sizes[$size][$portrate?0:1], self::$sizes[$size][$portrate?1:0])
		));
		$this->addObject($this->catalog = new PDFDict(
			'Type', '/Catalog',
			'Pages', $this->pages->link,
			'PageLayout', self::$layouts['continuous']
		));
		$this->addObject($this->resources = new PDFDict(
			'ProcSet', new PDFArray('/PDF', '/Text', '/ImageB', '/ImageC', '/ImageI'),
			'Font', new PDFDict(),
			'XObject', new PDFDict()
		));
		$this->addObject($this->info = new PDFDict(
			'Producer', new PDFString('FPDF '.FPDF_VERSION),
			'CreateDate', new PDFString('D:'.@date('YmdHis'))
		));
		
		$this->zoom = 'fullpage';
		$this->docPageSize = $size;
		$this->docPagePortrate = $portrate;
		$this->catalog->Kids = new PDFName('Pages');
	}
	
	public function __set($key, $value) {
		switch($key) {
			case 'title': return $this->info->Title->value = $this->utf8 ? $this->_UTF8toUTF16($value) : $value;
			case 'subject': return $this->info->Subject = $this->utf8 ? $this->_UTF8toUTF16($value) : $value;
			case 'author': return $this->Author = $this->utf8 ? $this->_UTF8toUTF16($value) : $value;
			case 'keywords': return $this->info->Keywords = $this->utf8 ? $this->_UTF8toUTF16($value) : $value;
			case 'creator': return $this->info->Creator = $this->utf8 ? $this->_UTF8toUTF16($value) : $value;
			case 'layout' :
				if (isset(self::$layouts[$value])) $this->catalog->PageLayout = self::$layouts[$value];
				break;
			case 'zoom':
				if (isset(self::$zooms[$value])) $this->zoom = $value; else  $zoom = (double)$zoom;
		}
		
	}

	public function output() {
		if ($this->pages->Count) {
			$act = isset(self::$zooms[$this->zoom])  ? self::$zooms[$this->zoom]
				: '/XYZ null null '.($this->zoom/100);
			$this->catalog->OpenAction = PDFArray($this->pages->Kids->items[0], $act);
		}
		
		$tx = "%PDF-1.6\n";
		$offs = array();
		foreach($this->objects as $o) {
			$offs[] = strlen($tx);
			$tx.=$o;
		}
		$xref = strlen($tx);
		$cnt = count($this->objects);
		$tx .= "xref\n0 $cnt\n";
		foreach($offs as $off) $tx.=$off?sprintf("%010d 00000 n \n",$off):"0000000000 65535 f \n";
		$tx .= "trailer\n";
		$tx .= (string)new PDFDict(array(
			'Size' => $cnt,
			'Root' => $this->catalog->link,
			'Info'=> $this->info->link));
		$tx .= "startxref\n$xref\n%%EOF\n";
	}

	public function AddPage($size = '', $portrate = true) {
		$this->addObject($page = new PDFDict(
			'Type', '/Page',
			'Parent', $this->pages->link,
			'Resourses', $this->resources->link
			
			
		));
		//$page-
	}
	
	function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='') {
		if(!isset($this->images[$file])) {
			//First use of this image, get info
			if($type=='') {
				if(false===($pos=strrpos($file, '.')))
					throw new EApp('Image file has no extension and no type was specified: '.$file);
				$type = substr($file, $pos + 1);
			}
			$type = strtolower($type);
			if($type == 'jpeg') $type = 'jpg';
			
			$mtd = 'loadImage'.$type;
			if(!method_exists($this,$mtd))
				throw new EApp('Unsupported image type: '.$type);
			list($iw, $ih, $data, $info) = $this->$mtd($file);
			$name = 'I'.count($this->images);
			$this->images[$file] = array($name, $iw, $ih);
		}
		else
			list($name, $iw, $ih) = $this->images[$file];
		
		list($w, $h, $x, $y) = toPt($w, $h, $x, $y);
	
		if($w==0 && $h==0) { //Automatic width and height calculation if needed
			$w=$iw;
			$h=$ih;
		}
		elseif($w==0) $w =$h * $iw/$h;
		elseif($h==0) $h =$w * $ih/$iw;

		if($y === null) { //Flowing mode
			if($this->y + $h > $this->PageBreakTrigger &&
			!$this->InHeader &&	!$this->InFooter &&	$this->autoPageBreak) {
				$saveX = $this->x;
				$this->addSamePage();
				$this->x = $saveX;
			}
			$y = $this->y;
			$this->y += $h;
		}
		if($x === null) $x = $this->x;
		
		$this->out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w, $h, $x, $this->h - ($y+$h), $name));
		if($link) $this->Link($x,$y,$w,$h,$link);
	
		$this->addObject($image = new PDFStream($data,
			'Type', 'XObject', 'Subtype', 'Image',
			'Width', $iw, 'Height', $ih,
			'BitsPerComponent', $info['bpc']
			
		));

		if($info['cs']=='Indexed') {
			$this->addObject($pal = new PDFStream($this->compress? gzcompress($info['pal']):$info['pal']));
			if ($this->compress) $pal->Filter = '/FlatDecode';
			$image->ColorSpace = new PDFArray('/Indexed', '/DeviceRGB', strlen($info['pal'])/3-1, $pal->link);
		}
		else {
			$image->ColorSpace = $info['cs'];
			if($info['cs']=='DeviceCMYK')
				$image->Device = '[1 0 1 0 1 0 1 0]'; //trick :)
		}

		$this->resources->XObject->$name = $image->link;

		if(isset($info['f'])) $this->out('/Filter /'.$info['f']);
		if(isset($info['parms'])) $this->out($info['parms']);
		if(isset($info['trns']) && is_array($info['trns']))	
			$this->out('/Mask ['.implode('', array_map(function ($a) {return "$a $a ";}, $info['trns'])).']');
	}
	
	public function AddFont($family, $style = FS_NONE, $file=''){ //Add a TrueType or Type1 font
		$family=strtolower($family);
		$styles = array('', 'I', 'B', 'BI');
		$style = $styles[$style];

		if($file=='') $file= str_replace(' ','',$family).strtolower($style).'.php';
		if($family=='arial') $family='helvetica';

		$fontkey=$family.$style;
		if(isset($this->fonts[$fontkey])) return;

		include($this->_getfontpath().$file);
	
		if(!isset($name)) throw new EApp('Could not include font definition file');
	
		$i=count($this->fonts)+1;
		$this->fonts[$fontkey] = array('i'=>$i, 'type'=>$type, 'name'=>$name, 'desc'=>$desc, 'up'=>$up, 'ut'=>$ut, 'cw'=>$cw, 'enc'=>$enc, 'file'=>$file);
	
		if ($diff) {	//Search existing encodings
			$d=0;
			foreach($this->diffs as $i=>$df) if($df==$diff) {
				$d = $i;
				break;
			}
			if($d==0) {
				$d = count($this->diff) + 1;
				$this->diffs[$d]=$diff;
			}
			$this->fonts[$fontkey]['diff'] = $d;
		}
		if($file) 
			$this->FontFiles[$file]	= $type=='TrueType'? array('length1'=>$originalsize) : array('length1'=>$size1, 'length2'=>$size2);


	$nf=$this->n;
	foreach($this->diffs as $diff)
	{
		//Encodings
		$this->_newobj();
		$this->out("<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences [$diff]>>", 'endobj');
	}
	foreach($this->FontFiles as $file=>$info)
	{
		//Font file embedding
		$this->_newobj();
		$this->FontFiles[$file]['n']=$this->n;
		$font='';
		$font = file_get_contents($this->_getfontpath().$file);
		$compressed=(substr($file,-2)=='.z');
		if(!$compressed && isset($info['length2']))
		{
			$header=(ord($font[0])==128);
			if($header)	{	//Strip first binary header
				$font=substr($font,6);
			}
			if($header && ord($font[$info['length1']])==128){	//Strip second binary header
				$font=substr($font,0,$info['length1']).substr($font,$info['length1']+6);
			}
		}
		$this->out('<</Length '.strlen($font));
		if($compressed)
			$this->out('/Filter /FlateDecode');
		$this->out('/Length1 '.$info['length1']);
		if(isset($info['length2']))
			$this->out('/Length2 '.$info['length2'].' /Length3 0');
		$this->out('>>');
		$this->putstream($font);
		$this->out('endobj');
	}
	foreach($this->fonts as $k=>$font)
	{
		//Font objects
		$this->fonts[$k]['n']=$this->n+1;
		$type=$font['type'];
		$name=$font['name'];
		if($type=='core'){
			//Standard font
			$this->newobj();
			$this->out('<</Type /Font', '/BaseFont /'.$name, '/Subtype /Type1');
			if($name!='Symbol' && $name!='ZapfDingbats')
				$this->out('/Encoding /WinAnsiEncoding');
			$this->out('>>', 'endobj');
		}
		elseif($type=='Type1' || $type=='TrueType')
		{
			//Additional Type1 or TrueType font
			$this->_newobj();
			$this->out('<</Type /Font');
			$this->out('/BaseFont /'.$name);
			$this->out('/Subtype /'.$type);
			$this->out('/FirstChar 32 /LastChar 255');
			$this->out('/Widths '.($this->n+1).' 0 R');
			$this->out('/FontDescriptor '.($this->n+2).' 0 R');
			if($font['enc'])
			{
				if(isset($font['diff']))
					$this->out('/Encoding '.($nf+$font['diff']).' 0 R');
				else
					$this->out('/Encoding /WinAnsiEncoding');
			}
			$this->out('>>','endobj');
			//Widths
			$this->_newobj();
			$cw=&$font['cw'];
			$s='[';
			for($i=32;$i<=255;$i++)	$s .= $cw[chr($i)].' ';
			$this->out($s.']', 'endobj');
			//Descriptor
			$this->_newobj();
			$s='<</Type /FontDescriptor /FontName /'.$name;
			foreach($font['desc'] as $k=>$v) $s.=" /$k $v";
			$file=$font['file'];
			if($file)
				$s.=' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
			$this->out($s.'>>', 'endobj');
		}
		else
		{
			//Allow for additional types
			$mtd='_put'.strtolower($type);
			if(!method_exists($this,$mtd))
				throw new EApp('Unsupported font type: '.$type);
			$this->$mtd($font);
		}
	}
}
	
	
	private function loadImageJpg($file) { 
		$a = GetImageSize($file);
		if(!$a)	throw new EApp('Missing or incorrect image file: '.$file);
		if($a[2]!=2) throw new EApp('Not a JPEG file: '.$file);
	
		$info = array(
			'cs'=>(!isset($a['channels']) || $a['channels']==3) ?'DeviceRGB' : ($a['channels']==4 ? 'DeviceGray'),
			'bpc'=>isset($a['bits']) ? $a['bits'] : 8;
			'f'=>'DCTDecode'
		):
		return array($a[0], $a[1], file_get_contents($file), $info);
	}

	//Extract info from a GIF file (via PNG conversion)
	private function loadImageGif($file) {
		if(!function_exists('imagepng')) throw new EApp('GD extension is required for GIF support');
		if(!function_exists('imagecreatefromgif')) throw new EApp('GD has no GIF read support');
		if(!(imagecreatefromgif($file))) throw new EApp('Missing or incorrect image file: '.$file);
		
		imageinterlace($im, 0);
		if(!($tmp=tempnam('.','gif'))) throw new EApp('Unable to create a temporary file');

		try {
			if(!imagepng($im, $tmp)) throw new EApp('Error while saving to temporary file');
			unlink($tmp); $tmp = false;
			imagedestroy($im);
			return = $this->loadImagePng($tmp);
		}
		catch (Exception $e) {
			if ($tmp) unlink($tmp);
			throw $e;
		}
	}

	private function loadImagePng($file) {	//Extract info from a PNG file
		if(!($f=fopen($file,'rb')))	throw new EApp('Can\'t open image file: '.$file);
		if(fread($f,8)!="\x89PNG\x0D\x0A\x1A\x0A") throw new EApp('Not a PNG file: '.$file);

		fseek($f, 4, SEEK_CUR);
		if(fread($f,4)!='IHDR') throw new EApp('Incorrect PNG file: '.$file);
	
		list($w, $h) = unpack('N*', fread($f, 8));
		list($bpc, $ct, $cmps, $flt, $intr) = unpack('c*', fread($f, 5))
	
		if($bpc > 8) throw new EApp('16-bit depth not supported: '.$file);
		
		if($ct==0) $colspace='DeviceGray';
		elseif($ct==2) $colspace='DeviceRGB';
		elseif($ct==3) $colspace='Indexed';
		else throw new EApp('Alpha channel not supported: '.$file);
		
		if($cmps!=0) throw new EApp('Unknown compression method: '.$file);
		if($flt!=0) throw new EApp('Unknown filter method: '.$file);
		if($intr!=0) throw new EApp('Interlacing not supported: '.$file);
		
		fseek($f, 4, SEEK_CUR);

		$data='';
		$info = array(
			'cs'=>$colspace,
			'bpc'=>$bpc,
			'f'=>'FlateDecode',
			'parms'=>'/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>');
		
		do {
			list(,$n) = unpack('Nc*', $this->_readstream($f, 4));
			$type = fread($f,4);
			if($type=='IEND') break;
			if($type=='PLTE') $info['pal'] = fread($f, $n);
			elseif($type=='IDAT') $data .= fread($f, $n);
			elseif($type=='tRNS') {	//Read transparency info
				$t = fread($f, $n);
				if($ct==0) $info['trns']=array(ord(substr($t,1,1)));
				elseif($ct==2) $info['trns']=array(ord(substr($t,1,1)), ord(substr($t,3,1)), ord(substr($t,5,1)));
				else if(($pos=strpos($t, chr(0)))!==false) $info['trns']=array($pos);
			}
			else fseek($f, $n, SEEK_CUR);
			
			fseek($f, 4, SEEK_CUR);
		} while($n);
		if($colspace=='Indexed' && empty($info['pal'])) throw new EApp('Missing palette in '.$file);
		fclose($f);

		return array($w, $h, $data, $info);
	}

	private function hexcolor($rgb, $front = true) {
		$b = array(0,17,34,51,68,85,102,119,136,153,'A'=>170,'B'=>187,'C'=>204,'D'=>221,'E'=>238,'F'=>255);
		$G = $front? 'g': 'G';
		$RG = $front? 'rg': 'RG';

		$l=strlen($rgb);
		list($r,$g,$b) = (3==$l) ? array_map(function ($v)use($b) {return $b[$v];}, str_split(strupper($rgb))):
			array_map(function ($v) {return hexdec($v);}, str_split(strupper($rgb), 2));

		return $r==$g && $g==$b ? sprintf('%.3F '.$G,$r/255) : sprintf('%.3F %.3F %.3F '.$RG,$r/255,$g/255,$b/255);					  
	}
	
	function toPt() {
		$values = func_get_args();
		foreach($values as &$value) {
			if (!preg_match(RE_SIZE, $value, $match))
				throw new EApp('Inccorect size', $value);
			$unit = isset($match[2])?$match[2]:'pt';
			$value = $match[0]/$self::units[$unit];
		}
		return $values;
	}
	
}

class FPDF {
	private $page = 0;               //current page number

	private $utf8 = false;
	private $title = 'Untitled';
	private $subject = 'No subject';
	private $author = 'FPDF';
	private $keywords = '';
	private $creator = 'FPDF';

	private $lMargin = 3;
	private $tMargin = 2;
	private $rMargin = 2;
	private $bMargin = 1.5;

	private $w,$h;               //dimensions of current page in user unit
	private $x,$y;               //current position in user unit
	private $lineWidth = .567;          //line width in pt unit

	private $drawColor = '0 G';          //commands for drawing color
	private $fillColor = '0 g';          //commands for filling color
	private $textColor = '0 G';          //commands for text color
	private $colorFlag = false;          //indicates whether fill and text colors are different

	private $aliasNbPages = '{nb}'; //alias for total number of pages
	private $zoom = 'fullwidth';           //zoom display mode
	private $layout = 'continuous';         //layout display mode

	
var $n = 2;                  //current object number
var $offsets;            //array of object offsets
private $buffer = '';             //buffer holding in-memory PDF
private $pages = array();              //array containing pages
private $pageSizes = array();              //array containing pages
var $state = 0;              //current document state
var $compress;           //compression flag
var $k;                  //scale factor (number of points in user unit)
var $DefOrientation;     //default orientation
var $CurOrientation;     //current orientation
var $PageFormats;        //available page formats
var $DefPageFormat;      //default page format
var $CurPageFormat;      //current page format
var $PageSizes;          //array storing non-default page sizes
var $wPt,$hPt;           //dimensions of current page in points
var $cMargin;            //cell margin
var $lasth = 0;              //height of last printed cell

var $fonts = array();              //array of used fonts
var $FontFiles = array();          //array of font files
var $diffs = array();              //array of encoding differences
var $FontFamily = '';         //current font family
var $FontStyle = '';          //current font style
var $underline = false;          //underlining flag
var $CurrentFont;        //current font info
private $FontSizePt = 12;         //current font size in points
var $FontSize;           //current font size in user unit
var $ws = 0;                 //word spacing
private $images = array();             //array of used images
var $PageLinks;          //array of links in pages
private $links = array();              //array of internal links
var $AutoPageBreak;      //automatic page breaking
var $PageBreakTrigger;   //threshold used to trigger page breaks
private $InHeader = false;           //flag set when processing header
private $InFooter = false;           //flag set when processing footer
	
	private $PDFVersion = 1.3;         //PDF version number

	static public $coreFonts = array(
		'courier'=>'Courier',
		'courierB'=>'Courier-Bold',
		'courierI'=>'Courier-Oblique',
		'courierBI'=>'Courier-BoldOblique',
		'helvetica'=>'Helvetica',
		'helveticaB'=>'Helvetica-Bold',
		'helveticaI'=>'Helvetica-Oblique',
		'helveticaBI'=>'Helvetica-BoldOblique',
		'times'=>'Times-Roman',
		'timesB'=>'Times-Bold',
		'timesI'=>'Times-Italic',
		'timesBI'=>'Times-BoldItalic',
		'symbol'=>'Symbol',
		'zapfdingbats'=>'ZapfDingbats');



/*******************************************************************************
*                                                                              *
*                               Public methods                                 *
*                                                                              *
*******************************************************************************/
function FPDF($orientation='P', $unit='mm', $format='A4')
{
	$this->_dochecks();

	if (!isset($units[$unit])) new EApp('Incorrect unit: '.$unit);
	$this->k = $units[$unit];

	$this->DefPageFormat = $this->CurPageFormat = $this->_getpageformat($format);
	$this->__set('orientation', $orientation);

	//Page orientation
	$orientation=strtolower($orientation);
	if($orientation=='p' || $orientation=='portrait')
	{
		$this->DefOrientation='P';
		$this->w=$this->DefPageFormat[0];
		$this->h=$this->DefPageFormat[1];
	}
	elseif($orientation=='l' || $orientation=='landscape')
	{
		$this->DefOrientation='L';
		$this->w=$this->DefPageFormat[1];
		$this->h=$this->DefPageFormat[0];
	}
	else
		throw new EApp('Incorrect orientation: '.$orientation);
	$this->CurOrientation=$this->DefOrientation;
	$this->wPt=$this->w*$this->k;
	$this->hPt=$this->h*$this->k;
	//Page margins (1 cm)
	$margin=28.35/$this->k;
	
	$this->SetMargins($margin,$margin);
	//Interior cell margin (1 mm)
	$this->cMargin=$margin/10;
	//Automatic page break
	$this->SetAutoPageBreak(true,2*$margin);
	//Enable compression
	$this->__set('compression', true);
}

	function __set($key, $value) {
		switch ($key) {
			case 'margin':
				if (!is_array($value)) $value = array($value);
				$s = sizeof($value);
				if ($s==4) { list($this->tMargin, $this->rMargin, $this->bMargin, $this->lMargin) = $value;}
				elseif ($s==3) {list($this->tMargin, $this->rMargin, $this->bMargin) = $value; $this->bMargin = $value[0];}
				elseif ($s==2) {list($this->tMargin, $this->lMargin) = $value; list($this->bMargin, $this->rMargin) = $value; }
				else {$this->tMargin=$this->lMargin=$this->bMargin=$this->rMargin = $value[0]; }
	
				if($this->page>0 && $this->x < $this->lMargin) $this->x = $this->lMargin;
				break;
			case 'compress': return $this->compress = function_exists('gzcompress') && $value;
			case 'utf8': return $this->utf8 = (bool)$value;
			case 'title': return $this->title = $this->utf8 ? $this->_UTF8toUTF16($value) : $value;
			case 'subject': return $this->subject = $this->utf8 ? $this->_UTF8toUTF16($value) : $value;
			case 'author': return $this->author = $this->utf8 ? $this->_UTF8toUTF16($value) : $value;
			case 'creator': return $this->creator = $this->utf8 ? $this->_UTF8toUTF16($value) : $value;
			case 'drawColor':
			case 'fillColor': 
			case 'textColor':
				$this->$key = $this->hexcolor($value, $key != 'fillColor');
				if($key!=='textColor' && $this->page > 0) $this->out($this->DrawColor);
				$this->ColorFlag=($this->fillColor!=$this->textColor);
				break;
			case 'x':return $this->x = $value >=0 ? $value : $this->w + $value;
			case 'y':
				$this->x=$this->lMargin;
				return $this->y = $value >= 0 ? $value : $this->h + $value;
			case 'aliasNbPages':
				return $this->$key = $value;
			case 'zoom':
				$zooms = array('fullpage','fullwidth','real','default');
				if (is_number($value) || in_array($value, $zooms)) return $this->zoom = $value;
				else throw new EApp('Incorrect zoom display mode: '.$value);
			case 'layout':
				$layouts = array('single', 'continuous', 'two', 'default');
				if (in_array($value, $layouts)) return $this->layout = $value;
				else throw new EApp('Incorrect layout display mode: '.$value);
			case 'linewidth':
				$this->lineWidth = $this->toPt($value);
				if($this->page>0) $this->out(sprintf('%.2F w',$this->lineWidth));
				break;
		}
	}
				

function SetAutoPageBreak($auto, $margin=0)
{
	//Set auto page break mode and triggering margin
	$this->AutoPageBreak=$auto;
	$this->bMargin=$margin;
	$this->PageBreakTrigger=$this->h-$margin;
}

function open(){
	$this->state=1;
}

function close(){
	//Terminate document
	if($this->state == 3) return;
	if($this->page == 0) $this->AddPage();

	//Page footer
	$this->InFooter=true;
	$this->Footer();
	$this->InFooter=false;

	$this->_endpage();
	$this->_enddoc();
}

function AddPage($orientation='', $format=''){
	//Start a new page
	if($this->state==0) $this->open();
	
	$family=$this->FontFamily;
	$style=$this->FontStyle.($this->underline ? 'U' : '');
	$size=$this->FontSizePt;
	$lw=$this->LineWidth;
	$dc=$this->DrawColor;
	$fc=$this->FillColor;
	$tc=$this->TextColor;
	$cf=$this->ColorFlag;
	if($this->page>0)
	{
		//Page footer
		$this->InFooter=true;
		$this->Footer();
		$this->InFooter=false;
		//Close page
		$this->_endpage();
	}
	//Start new page
	$this->_beginpage($orientation,$format);
	//Set line cap style to square
	$this->out('2 J');
	//Set line width
	$this->LineWidth=$lw;
	$this->out(sprintf('%.2F w',$lw));
	//Set font
	if($family)
		$this->SetFont($family,$style,$size);
	//Set colors
	$this->DrawColor=$dc;
	if($dc!='0 G')	$this->out($dc);
	$this->FillColor=$fc;
	if($fc!='0 g')	$this->out($fc);
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;
	//Page header
	$this->InHeader=true;
	$this->Header();
	$this->InHeader=false;
	//Restore line width
	if($this->LineWidth!=$lw)
	{
		$this->LineWidth=$lw;
		$this->out(sprintf('%.2F w',$lw));
	}
	//Restore font
	if($family)
		$this->SetFont($family,$style,$size);
	//Restore colors
	if($this->DrawColor!=$dc)
	{
		$this->DrawColor=$dc;
		$this->out($dc);
	}
	if($this->FillColor!=$fc)
	{
		$this->FillColor=$fc;
		$this->out($fc);
	}
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;
}

	public function Header() { /* To be implemented in your own inherited class */ }
	public function Footer() { /* To be implemented in your own inherited class */ }

	function __get($key) {
		switch($key) {
			case 'page':
			case 'x':
			case 'y':
			case 'autoPageBreak':
				return $this->$key;
			
			default: return parent::__get($key);
		}
	}

function GetStringWidth($s){
	//Get width of a string in the current font
	$s=(string)$s;
	$cw=&$this->CurrentFont['cw'];
	$w=0;
	$l=strlen($s);
	for($i=0;$i<$l;$i++)
		$w+=$cw[$s[$i]];
	return $w*$this->FontSize/1000;
}


// DRAW
function Line($x1, $y1, $x2, $y2){
	$this->out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
}

// sample 2pt 3pt 4pt 4pt or array
function Rect($rect, $style=BR_NONE) {
	if (is_string($rect)) $rect = explode(' ', $rect);
	list($x, $y, $w, $h) = $rect;
	$border = array('S', 'f', 'B');
	$this->out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$border[$style]));
}

function Text($x, $y, $txt){
	//Output a string
	$s=sprintf('BT %.2F %.2F Td %s Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_textstring($txt));
	if($this->underline && $txt!='')
		$s.=' '.$this->_dounderline($x,$y,$txt);
	if($this->ColorFlag)$s="q {$this->TextColor} $s Q";
	$this->out($s);
}


function SetFont($family, $style = FS_NONE, $size=0){
	//Select a font; size given in points
	global $fpdf_charwidths;

	$family=strtolower($family);
	if($family=='')	$family=$this->FontFamily;
	if($family=='arial') $family='helvetica';
	elseif($family=='symbol' || $family=='zapfdingbats') $style= FS_NONE;
	$style=strtoupper($style);
	if(strpos($style,'U')!==false)
	{
		$this->underline=true;
		$style=str_replace('U','',$style);
	}
	else
		$this->underline=false;
	if($style=='IB')
		$style='BI';
	if($size==0)
		$size=$this->FontSizePt;
	//Test if font is already selected
	if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
		return;
	//Test if used for the first time
	$fontkey=$family.$style;
	if(!isset($this->fonts[$fontkey]))
	{
		//Check if one of the standard fonts
		if(isset($this->CoreFonts[$fontkey]))
		{
			if(!isset($fpdf_charwidths[$fontkey]))
			{
				//Load metric file
				$file=$family;
				if($family=='times' || $family=='helvetica')
					$file.=strtolower($style);
				include($this->_getfontpath().$file.'.php');
				if(!isset($fpdf_charwidths[$fontkey]))
					throw new EApp('Could not include font metric file');
			}
			$i=count($this->fonts)+1;
			$name=$this->CoreFonts[$fontkey];
			$cw=$fpdf_charwidths[$fontkey];
			$this->fonts[$fontkey]=array('i'=>$i, 'type'=>'core', 'name'=>$name, 'up'=>-100, 'ut'=>50, 'cw'=>$cw);
		}
		else
			throw new EApp('Undefined font: '.$family.' '.$style);
	}
	//Select it
	$this->FontFamily=$family;
	$this->FontStyle=$style;
	$this->CurrentFont=&$this->fonts[$fontkey];
	$this->FontSizePt = 0;
	$this->SetFontSize($size);
}

function SetFontSize($size){
	//Set font size in points
	if($this->FontSizePt==$size) return;
	
	$this->FontSizePt=$size;
	$this->FontSize=$size/$this->k;
	if($this->page>0)
		$this->out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

function AddLink(){
	//Create a new internal link
	$n = count($this->links) + 1;
	$this->links[$n]=array(0, 0);
	return $n;
}

function SetLink($link, $y=0, $page=-1){
	//Set destination of internal link
	if($y==-1) $y=$this->y;
	if($page==-1) $page=$this->page;
	$this->links[$link]=array($page, $y);
}

function Link($x, $y, $w, $h, $link){
	//Put a link on the page
	$this->PageLinks[$this->page][]=array($x*$this->k, $this->hPt-$y*$this->k, $w*$this->k, $h*$this->k, $link);
}

function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link=''){
	//Output a cell
	$k=$this->k;
	if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
	{
		//Automatic page break
		$x=$this->x;
		$ws=$this->ws;
		if($ws>0)
		{
			$this->ws=0;
			$this->out('0 Tw');
		}
		$this->AddPage($this->CurOrientation,$this->CurPageFormat);
		$this->x=$x;
		if($ws>0)
		{
			$this->ws=$ws;
			$this->out(sprintf('%.3F Tw',$ws*$k));
		}
	}
	if($w==0)
		$w=$this->w-$this->rMargin-$this->x;
	$s='';
	if($fill || $border==1)
	{
		if($fill)
			$op=($border==1) ? 'B' : 'f';
		else
			$op='S';
		$s=sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	}
	if(is_string($border))
	{
		$x=$this->x;
		$y=$this->y;
		if(strpos($border,'L')!==false)
			$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
		if(strpos($border,'T')!==false)
			$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
		if(strpos($border,'R')!==false)
			$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
		if(strpos($border,'B')!==false)
			$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	}
	if($txt!=='')
	{
		if($align=='R')
			$dx=$w-$this->cMargin-$this->GetStringWidth($txt);
		elseif($align=='C')
			$dx=($w-$this->GetStringWidth($txt))/2;
		else
			$dx=$this->cMargin;
		if($this->ColorFlag)
			$s.='q '.$this->TextColor.' ';
		$txt2=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
		$s.=sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$txt2);
		if($this->underline)
			$s.=' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
		if($this->ColorFlag)
			$s.=' Q';
		if($link)
			$this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
	}
	if($s)
		$this->out($s);
	$this->lasth=$h;
	if($ln>0)
	{
		//Go to next line
		$this->y+=$h;
		if($ln==1)
			$this->x=$this->lMargin;
	}
	else
		$this->x+=$w;
}

function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false){
	//Output text with automatic or explicit line breaks
	$cw=&$this->CurrentFont['cw'];
	if($w==0)
		$w=$this->w-$this->rMargin-$this->x;
	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	$s=str_replace("\r",'',$txt);
	$nb=strlen($s);
	if($nb>0 && $s[$nb-1]=="\n")
		$nb--;
	$b=0;
	if($border)
	{
		if($border==1)
		{
			$border='LTRB';
			$b='LRT';
			$b2='LR';
		}
		else
		{
			$b2='';
			if(strpos($border,'L')!==false)	$b2.='L';
			if(strpos($border,'R')!==false)	$b2.='R';
			
			$b=(strpos($border,'T')!==false) ? $b2.'T' : $b2;
		}
	}
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$ns=0;
	$nl=1;
	while($i<$nb)
	{
		//Get next character
		$c=$s[$i];
		if($c=="\n")
		{
			//Explicit line break
			if($this->ws>0)
			{
				$this->ws=0;
				$this->out('0 Tw');
			}
			$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border && $nl==2)
				$b=$b2;
			continue;
		}
		if($c==' ')
		{
			$sep=$i;
			$ls=$l;
			$ns++;
		}
		$l+=$cw[$c];
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1)
			{
				if($i==$j)
					$i++;
				if($this->ws>0)
				{
					$this->ws=0;
					$this->out('0 Tw');
				}
				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			}
			else
			{
				if($align=='J')
				{
					$this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
					$this->out(sprintf('%.3F Tw',$this->ws*$this->k));
				}
				$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
				$i=$sep+1;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border && $nl==2)
				$b=$b2;
		}
		else
			$i++;
	}
	//Last chunk
	if($this->ws>0)
	{
		$this->ws=0;
		$this->out('0 Tw');
	}
	if($border && strpos($border,'B')!==false)
		$b.='B';
	$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
	$this->x=$this->lMargin;
}

function Write($h, $txt, $link=''){
	//Output text in flowing mode
	$cw=&$this->CurrentFont['cw'];
	$w=$this->w-$this->rMargin-$this->x;
	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	$s=str_replace("\r",'',$txt);
	$nb=strlen($s);
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$nl=1;
	while($i<$nb)
	{
		//Get next character
		$c=$s[$i];
		if($c=="\n")
		{
			//Explicit line break
			$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			}
			$nl++;
			continue;
		}
		if($c==' ')
			$sep=$i;
		$l+=$cw[$c];
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1)
			{
				if($this->x>$this->lMargin)
				{
					//Move to next line
					$this->x=$this->lMargin;
					$this->y+=$h;
					$w=$this->w-$this->rMargin-$this->x;
					$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
					$i++;
					$nl++;
					continue;
				}
				if($i==$j)
					$i++;
				$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
			}
			else
			{
				$this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',0,$link);
				$i=$sep+1;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			}
			$nl++;
		}
		else
			$i++;
	}
	//Last chunk
	if($i!=$j)
		$this->Cell($l/1000*$this->FontSize,$h,substr($s,$j),0,0,'',0,$link);
}

function Ln($h = null){
	//Line feed; default value is last cell height
	$this->x = $this->lMargin;
	$this->y += $h===null ? $this->lasth : $h;
}



function SetXY($x, $y)
{
	//Set x and y positions
	$this->SetY($y);
	$this->SetX($x);
}

function output($name='doc.pdf', $dest='I') {
	//Output PDF to some destination
	if ($this->state < 3) $this->close();
	
	switch($dest) {
		case 'I': //Send to standard output or Download file
		case 'D':
			if(ob_get_length())
				throw new EApp('Some data has already been output, can\'t send PDF file');
			if(php_sapi_name()!='cli' || $dest=='D') {
				//We send to a browser
				header('Content-Type: '.($dest=='D'?'application/x-download' : 'application/pdf'));
				if(headers_sent())	throw new EApp('Some data has already been output, can\'t send PDF file');

				header('Content-Length: '.strlen($this->buffer));
				header('Content-Disposition: inline; filename="'.$name.'"');
				header('Cache-Control: private, max-age=0, must-revalidate');
				header('Pragma: public');
				ini_set('zlib.output_compression','0');
			}
			echo $this->buffer;
			break;
		case 'F': //Save to local file
			$f=fopen($name,'wb');
			if(!$f) throw new EApp('Unable to create output file: '.$name);
			fwrite($f, $this->buffer);
			fclose($f);
			break;
		case 'S': //Return as a string
			return $this->buffer;
		default:
			throw new EApp('Incorrect output destination: '.$dest);
	}
	return true;
}

/*******************************************************************************
*                                                                              *
*                              Protected methods                               *
*                                                                              *
*******************************************************************************/
function _getpageformat($format) {
	$format=strtolower($format);
	if(!isset(self::pageFormats[$format])) throw new EApp('Unknown page format: '.$format);
	$a=self::pageFormats[$format];
	return array($a[0]/$this->k, $a[1]/$this->k);
}

function _getfontpath()
{
	if(!defined('FPDF_FONTPATH') && is_dir(dirname(__FILE__).'/font'))
		define('FPDF_FONTPATH',dirname(__FILE__).'/font/');
	return defined('FPDF_FONTPATH') ? FPDF_FONTPATH : '';
}

function _beginpage($orientation, $format)
{
	$this->page++;
	$this->pages[$this->page]='';
	$this->state=2;
	$this->x=$this->lMargin;
	$this->y=$this->tMargin;
	$this->FontFamily='';
	//Check page size
	if($orientation=='')
		$orientation=$this->DefOrientation;
	else
		$orientation=strtoupper($orientation[0]);
	if($format=='')
		$format=$this->DefPageFormat;
	else
	{
		if(is_string($format))
			$format=$this->_getpageformat($format);
	}
	if($orientation!=$this->CurOrientation || $format[0]!=$this->CurPageFormat[0] || $format[1]!=$this->CurPageFormat[1])
	{
		//New size
		if($orientation=='P')
		{
			$this->w=$format[0];
			$this->h=$format[1];
		}
		else
		{
			$this->w=$format[1];
			$this->h=$format[0];
		}
		$this->wPt=$this->w*$this->k;
		$this->hPt=$this->h*$this->k;
		$this->PageBreakTrigger=$this->h-$this->bMargin;
		$this->CurOrientation=$orientation;
		$this->CurPageFormat=$format;
	}
	if($orientation!=$this->DefOrientation || $format[0]!=$this->DefPageFormat[0] || $format[1]!=$this->DefPageFormat[1])
		$this->PageSizes[$this->page]=array($this->wPt, $this->hPt);
}

function _endpage() { $this->state=1; }

function _textstring($s) {
}

function UTF8toUTF16($s) {
	//Convert UTF-8 to UTF-16BE with BOM
	$res="\xFE\xFF";
	$nb=strlen($s);
	$i=0;
	while($i < $nb) {
		$c0 = 0;
		$c1= ord($s[$i++]);
		if($c1 >= 224 && $nb - $i > 1) { //3-byte character
			$c2 = ord($s[$i++]) & 0x3F;
			$c0 = ($c1 & 0x0F << 4) + ($c2 >> 2)
			$c1 = (ord($s[$i++]) & 0x3F) + ($c2 & 0x03 << 6);
		}
		elseif($c1 >= 192 && $nb - $i) { //2-byte character
			$c0 = $c1 & 0x1C >>2;
			$c1= ($c1 & 0x03 << 6) + (ord($s[$i++]) & 0x3F);
		}
		$res.=pack('cc', $c0 , $c1);
	}
	return $res;
}

function _dounderline($x, $y, $txt)
{
	//Underline text
	$up=$this->CurrentFont['up'];
	$ut=$this->CurrentFont['ut'];
	$w=$this->GetStringWidth($txt)+$this->ws*substr_count($txt,' ');
	return sprintf('%.2F %.2F %.2F %.2F re f',$x*$this->k,($this->h-($y-$up/1000*$this->FontSize))*$this->k,$w*$this->k,-$ut/1000*$this->FontSizePt);
}

function _newobj() {
	//Begin a new object
	$this->n++;
	$this->offsets[$this->n] = strlen($this->buffer);
	$this->out($this->n.' 0 obj');
}

function _putstream($s) {
	$this->out('stream', $s,'endstream');
}

function out() {
	$a = func_get_args();
	if($this->state==2)	$this->pages[$this->page] .= implode("\n", $a)."\n";
	else $this->buffer .= implode("\n", $a)."\n";
}

function _putpages()
{
	$nb=$this->page;
	if(!empty($this->AliasNbPages))
	{
		//Replace number of pages
		for($n=1;$n<=$nb;$n++)
			$this->pages[$n]=str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
	}
	if($this->DefOrientation=='P')
	{
		$wPt=$this->DefPageFormat[0]*$this->k;
		$hPt=$this->DefPageFormat[1]*$this->k;
	}
	else
	{
		$wPt=$this->DefPageFormat[1]*$this->k;
		$hPt=$this->DefPageFormat[0]*$this->k;
	}
	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
	for($n=1;$n<=$nb;$n++)
	{
		//Page
		$this->_newobj();
		$this->out('<</Type /Page', '/Parent 1 0 R');
		if(isset($this->PageSizes[$n]))
			$this->out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageSizes[$n][0],$this->PageSizes[$n][1]));
		$this->out('/Resources 2 0 R');
		if(isset($this->PageLinks[$n]))
		{
			//Links
			$annots='/Annots [';
			foreach($this->PageLinks[$n] as $pl){
				$rect = sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
				$annots.="<</Type /Annot /Subtype /Link /Rect [$rect] /Border [0 0 0] ";
				if(is_string($pl[4]))
					$annots.='/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
				else
				{
					$l=$this->links[$pl[4]];
					$h=isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
					$annots.=sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',1+2*$l[0],$h-$l[1]*$this->k);
				}
			}
			$this->out($annots.']');
		}
		$this->out('/Contents '.($this->n+1).' 0 R>>');
		$this->out('endobj');
		//Page content
		$p=($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
		$this->_newobj();
		$this->out('<<'.$filter.'/Length '.strlen($p).'>>');
		$this->putstream($p);
		$this->out('endobj');
	}
	//Pages root
	$this->offsets[1]=strlen($this->buffer);
	$kids='';
	for($i=0;$i<$nb;$i++)
		$kids .= (3+2*$i).' 0 R ';
	$this->out('1 0 obj', '<</Type /Pages', "/Kids [$kids]", '/Count '.$nb, sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt), '>>', 'endobj');
}

function _putresources(){
	$this->_putfonts();
	$this->_putimages();
	//Resource dictionary
	$this->offsets[2]=strlen($this->buffer);
	$this->out('2 0 obj');
	$this->out('<<');
	$this->out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
	$this->out('/Font <<');
	foreach($this->fonts as $font) $this->out("/F{$font['i']} {$font['n'] 0 R");
	$this->out('>>');
	$this->out('/XObject <<');
	foreach($this->images as $image) $this->out("/I{$image['i']} {$image['n']} 0 R");
	$this->out('>>');
	$this->out('>>');
	$this->out('endobj');
}

function _enddoc() {
	$this->out('%PDF-'.$this->PDFVersion);
	$this->_putpages();
	$this->_putresources();
	$this->_putinfo();
	$this->_putcatalog();
	$o=strlen($this->buffer);
	$this->out('xref');
	$this->out('0 '.($this->n+1));
	$this->out('0000000000 65535 f ');
	for($i=1;$i<=$this->n;$i++)
		$this->out(sprintf('%010d 00000 n ',$this->offsets[$i]));
	//Trailer
	$this->out('trailer');
	$this->out('<<');
	$this->out('/Size '.($this->n+1));
	$this->out('/Root '.$this->n.' 0 R');
	$this->out('/Info '.($this->n-1).' 0 R');
	$this->out('>>');
	$this->out('startxref');
	$this->out($o);
	$this->out('%%EOF');
	$this->state=3;
}
//End of class
}

//Handle special IE contype request
if(isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']=='contype')
{
	header('Content-Type: application/pdf');
	exit;
}

?>
