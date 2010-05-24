<?php
namespace FW;

/*
 Excel (*.XLS) Reader (BIFF5 & BIFF8) Excel 5.0+

 current version: 0.2

 roadmap:
 version 1.0 read data
 version 2.0 write data
 version 3.0 create html
*/


define('XR_BIFF8',            0x0600);
define('XR_BIFF5',            0x0500);
define('XR_WORKBOOKGLOBALS',  0x0005);
define('XR_WORKSHEET',        0x0010);

define('XR_REC_BOF',          0x0809);
define('XR_REC_EOF',          0x000a);
define('XR_REC_BOUNDSHEET',   0x0085);
define('XR_REC_DIMENSION',    0x0200);
define('XR_REC_ROW',          0x0208);
define('XR_REC_DBCELL',       0x00d7);
define('XR_REC_FILEPASS',     0x002f);
define('XR_REC_NOTE',         0x001c);
define('XR_REC_TXO',          0x01b6);
define('XR_REC_RK',           0x027e);
define('XR_REC_BLANK',        0x0201);
define('XR_REC_MULRK',        0x00bd);
define('XR_REC_MULBLANK',     0x00be);
define('XR_REC_INDEX',        0x020b);
define('XR_REC_SST',          0x00fc);
define('XR_REC_EXTSST',       0x00ff);
define('XR_REC_CONTINUE',     0x003c);
define('XR_REC_LABEL',        0x0204);
define('XR_REC_LABELSST',     0x00fd);
define('XR_REC_NUMBER',       0x0203);
define('XR_REC_NAME',         0x0018);
define('XR_REC_ARRAY',        0x0221);
define('XR_REC_STRING',       0x0207);
define('XR_REC_FORMULA',      0x0006);
define('XR_REC_FORMAT',       0x041e);
define('XR_REC_XF',           0x00e0);
define('XR_REC_BOOLERR',      0x0205);
define('XR_REC_UNKNOWN',      0xffff);
define('XR_REC_NINETEENFOUR', 0x0022);
define('XR_REC_MERGEDCELLS',  0x00E5);
define('XR_REC_CODEPAGE',     0x0042);
define('XR_REC_WINDOW1',      0x003d);
define('XR_REC_PALETTE',      0x0092);
define('XR_REC_FONT',         0x0031);
define('XR_REC_INTERFACEHDR', 0x00E1);
define('XR_REC_INTERFACEEND', 0x00E2);
define('XR_REC_MMS',		  0x00C1);
define('XR_REC_WRITEACCESS',  0x005C);
define('XR_REC_DSF',		  0x0161);
define('XR_REC_RECALCID',	  0x01C1);
define('XR_REC_EXCEL9FILE',   0x01C0);
define('XR_REC_TABID',		  0x013D);
define('XR_REC_FNGROUPCOUNT', 0x009C);
define('XR_REC_WINDOWPROTECT',0x0019);
define('XR_REC_PROTECT', 	  0x0012);
define('XR_REC_PASSWORD',	  0x0013);
define('XR_REC_PROT4REV',	  0x01AF);
define('XR_REC_PROT4REVPASS', 0x01BC);
define('XR_REC_BACKUP', 	  0x0040);
define('XR_REC_HIDEOBJ', 	  0x008D);
define('XR_REC_PRECISION',	  0x000E);
define('XR_REC_REFRESHALL',	  0x01B7);
define('XR_REC_BOOKBOOL',	  0x00DA);
define('XR_REC_XFCRC',		  0x087C);
define('XR_REC_XFEXT',	  	  0x087D);
define('XR_REC_STYLE',		  0x0293);
define('XR_REC_STYLEEXT', 	  0x0892);
define('XR_REC_TABLESTYLES',  0x088E);
define('XR_REC_TABLESTYLE',   0x088F);
define('XR_REC_TABLESTYLEELEMENT', 0x0890);
define('XR_REC_MTRSETTINGS',  0x089A);
define('XR_REC_FORCEFULLCALCULATION', 0x08A3);
define('XR_REC_COUNTRY',      0x008C);
define('XR_REC_USESELFS',     0x0160);
define('XR_REC_BOOKEXT',      0x0863);
define('XR_REC_THEME',        0x0896);
define('XR_REC_COMPRESSPICTURES', 0x089B);
define('XR_REC_COMPAT12',     0x088C);
define('XR_REC_CALCCOUNT',    0x000C);
define('XR_REC_CALCMODE',     0x000D);
define('XR_REC_REFMODE',      0x000F);
define('XR_REC_ITERATION',    0x0011);
define('XR_REC_DELTA',        0x0010);
define('XR_REC_SAVERECALC',   0x005F);
define('XR_REC_PRINTHEADERS', 0x002A);
define('XR_REC_PRINTGRIDLINES', 0x002B);
define('XR_REC_GRIDSET',      0x0082);
define('XR_REC_GUTS',         0x0080);
define('XR_REC_DEFAULTROWHEIGHT', 0x0225);
define('XR_REC_HEADER',       0x0014);
define('XR_REC_FOOTER',       0x0015);
define('XR_REC_HCENTER',      0x0083);
define('XR_REC_VCENTER',      0x0084);
define('XR_REC_LEFTMARGIN',   0x0026);
define('XR_REC_RIGHTMARGIN',  0x0027);
define('XR_REC_TOPMARGIN',    0x0028);
define('XR_REC_BOTTOMMARGIN', 0x0029);
define('XR_REC_PLS', 		  0x004D);
define('XR_REC_VERTICALPAGEBREAKS', 0x001A);
define('XR_REC_HEADERFOOTER', 0x089C);
define('XR_REC_DEFCOLWIDTH',  0x0055);
define('XR_REC_SETUP',        0x00A1);
define('XR_REC_PLV', 		  0x088B);
define('XR_REC_STANDARDWIDTH', 0x0099);
define('XR_REC_WINDOW2', 	  0x023E);
define('XR_REC_SELECTION',	  0x001D);
define('XR_REC_FEATHEADR',	  0x0867);
define('XR_REC_COLINFO', 	  0x007d);
define('XR_REC_WSBOOL',		  0x0081);
define('XR_REC_CLRTCLIENT',	  0x105C);

define('XR_UTCOFFSETDAYS' ,    25569);
define('XR_UTCOFFSETDAYS1904', 24107);
define('XR_MSINADAY',          86400);
//define('XR_MSINADAY', 24 * 60 * 60);

//define('XR_DEF_NUM_FORMAT', "%.2f");
define('XR_DEF_NUM_FORMAT',    "%s");

// header
define('NUM_BIG_BLOCK_DEPOT_BLOCKS_POS', 0x2c);
define('ROOT_START_BLOCK_POS', 0x30);
define('SMALL_BLOCK_DEPOT_BLOCK_POS', 0x3c);
define('EXTENSION_BLOCK_POS', 0x44);
define('NUM_EXTENSION_BLOCK_POS', 0x48);
define('BIG_BLOCK_DEPOT_BLOCKS_POS', 0x4c);

// signature
define('IDENTIFIER_OLE', pack("CCCCCCCC",0xd0,0xcf,0x11,0xe0,0xa1,0xb1,0x1a,0xe1));

// sizes
define('SMALL_BLOCK_SIZE', 0x40);
define('BIG_BLOCK_SIZE', 0x200);
define('SMALL_BLOCK_THRESHOLD', 0x1000);
define('PROPERTY_STORAGE_BLOCK_SIZE', 0x80);

// property storage offsets
define('SIZE_OF_NAME_POS', 0x40);
define('TYPE_POS', 0x42);
define('START_BLOCK_POS', 0x74);
define('SIZE_POS', 0x78);

// todo @createSt
class TableStyle {
	public $type;
	public $elements = array();
}

class TableStyleElement {
	public $type;
	public $size;
	public $DXFindex;
}

class XF {
	public $font;
	public $format;
	static $textAlignments = array('', 'Left', 'Center', 'Right', 'Filled', 'Justify', 'Centred-across-selection', 'Distributed');
	static $vertialAlignments = array('Top', 'Center', 'Bottom', 'Justified', 'Distributed');
	
	function __construct($stream = false) {
		if ($stream) $this->load($stream);
	}
	
	function load($stream) {
		$info = $stream->readf('vfont/vformat/vtype/calign');
		$this->font = $info['font'];
		$this->format = $info['format'];
		$this->locked = $info['type'] & 1;
		$this->formulaHidden = $info['type'] & 2;
		$this->style = $info['type'] & 4;
		$this->parentId = $info['type'] >> 3;
		
		$this->textAlign = $info['align'] & 7;
		$this->textWrapped = $info['align'] & 8;
		$this->verticalAlign = ($info['align'] & 0x70) >> 4;
		$this->justifyLastLine = $info['align'] & 0x80;
		
		$rot = array(0=>0, 1=>255, 2=>90, 3=>-90);
		
		if ($stream->BIFF8) {
			$this->rotation = $stream->getByte();
			$this->indent = ($b = $stream->getByte()) & 0x0F;
			$this->textShrink = $b & 0x10;
			$this->textDirection = $b  >> 6;
			$this->userAttr = $stream->getByte();
			$a = $stream->getDword();
			$b = $stream->getDword();

			$this->borderColor = array(
				($b & 0x0000007F),
				(($a & 0x3F800000) >> 23),
				(($b & 0x00003F80) >> 7),
				(($a & 0x007F0000) >> 16)
			);
			$this->borderStyle = array(
				(($a & 0x00000F00) >> 08),
				(($a & 0x000000F0) >> 04),
				(($a & 0x0000F000) >> 12),
				($a & 0x0000000F)
			);
			$this->diagonalType = (($a & 0xC0000000) >> 30);
			$this->diagonalColor = (($b & 0x001F000) >> 14);
			$this->diagonalStyle = (($b & 0x001F000) >> 14);

			$c = $stream->getWord();
			$this->pattetnType = (($b & 0xFC000000) >> 26);
			$this->patternBgcolor = (($c & 0x00003F80) >> 7);
			$this->patternFgcolor = ($c & 0x0000007F);
		}
		else {
			$b = $s->getByte();
			$this->rotation = $rot[$b & 3];
			$this->userAttr = $b >> 2;
			list(,$a, $b) = $stream->readf('V*');

			$this->borderColor = array(
				($b & 0x0000FE00 >> 9),
				($b & 0x3F800000 >> 23),
				($a & 0xFE000000 >> 25),
				($b & 0x007F0000 >> 16)
			);
			$this->borderStyle = array(
				($b & 0x00000007),
				($b & 0x000001C0 >> 06),
				($a & 0x01C00000 >> 22),
				($b & 0x00000038 >> 03)
			);
			
			$this->pattetnType = ($a & 0x0003F0000 >> 16);
			$this->patternBgcolor = ($a & 0x00003F80 >> 7);
			$this->patternFgcolor = ($a & 0x0000007F);
			$this->diagonalType = 0;
		}
		
		// @todo Apply parent styles 
		/*  for cellXF  clearbit -> use parent, styleXF setbit-> use parent
		0 01H Flag for number format
		1 02H Flag for font
		2 04H Flag for horizontal and vertical alignment, text wrap, indentation, text direction
		3 08H Flag for border lines
		4 10H Flag for background area style
		5 20H Flag for cell protection (cell locked and formula hidden)						
		*/
		
	}
}

class Font {
	public $name;
	public $weight;

	function __construct($stream = false) {
		if ($stream) {
			$this->load($stream);
		}
	}
	
	function load($stream) {
		$this->height = $stream->getWord();
		$type = $stream->getWord();
		$this->color = $stream->getWord();
		$this->weight = $stream->getWord();
		$this->subscript = $stream->getWord();
		$this->underline = $stream->getByte();
		$this->family = $stream->getByte();
		$this->charset = $stream->getByte();
		$this->res = $stream->getByte();
		$len = $stream->getByte();
		$this->name = $stream->getString($len);
		$this->bold = ($type & 0x0001) | ($this->weight >= 700);
		$this->italic = $type & 0x0002;
		$this->underlined = $type & 0x0004;
		$this->strkeout = $type & 0x0008;
		$this->outline = $type & 0x0010;
		$this->shadow = $type & 0x0020;
		$this->condensed = $type & 0x0040;
		$this->extended = $type & 0x0080;
	}
	
	
	function __toString() {
		return (string)$this->name;
	}
}

class Cell {
	public $format;
	public $data;
	function __construct($format, $data) {
		$this->format = $format;
		$this->data = $data;
	}
	function __toString() {
		return $data;
	}
}

class Row {
	public $format;
	public $cells;
	public $options;
	public $height;

	function __construct($stream = false) {
		$this->cells = array();
		if ($stream) {
			$info = $stream->readf('vno/vcolmin/vcolmax/vheight/virw/vres/voptions/vxf');
			// colmin, colmax, irw, res not used
			$this->index = $info['no'];
			$this->height = $info['height'];
			$this->options = $info['options'];
			$this->format = $info['xf'];
		}
	}
}

class RichString {
	public $string;
	public $rt;

	function __construct($string, $runs) {
		$this->string = $string;
		$this->rt = $runs;
	}

	function __toString() {
		return $this->string;
	}
}

class XLSheet {
	static $boolValues = array('NO', 'YES');
	static $errValues = array(
			0x00 => '#NULL!', #Intersection of two cell ranges is empty
			0x07 => '#DIV/0!', # Division by zero
			0x0F => '#VALUE!', # Wrong type of operand
			0x17 => '#REF!', # Illegal or deleted cell reference
			0x1D => '#NAME?', # Wrong function or range name
			0x24 => '#NUM!', # Value range overflow
			0x2A => '#N/A' #
		);

	private $xl;

	function __construct($xl, $name) {
		$this->name = $name;
		$this->xl = $xl;
		$this->rows = array();
		$this->index = array();
	}

	function load($stream) {
		$xl = $this->xl;
		$stream->getRec(XR_REC_BOF);
		$version = $stream->getWord();
		if ($stream->getWord() != XR_WORKSHEET) throw new EApp("Shit!");
		$code = $stream->getRec(XR_REC_INDEX);
		while ($code!=XR_REC_EOF) {
			switch ($code) {
				case XR_REC_ROW:
					$row = new Row($stream);
					$this->rows[$row->index] = $row;
					break;

				case XR_REC_MERGEDCELLS:
					//@todo merged cells;
					break;

				case XR_REC_MULBLANK:
					$info = $stream->readf('v*');
					$row = array_shift($info);
					$col = array_shift($info);
					$last = array_pop($info);
					foreach($info  as $xf) $this->rows[$row]->cells[$col++]= new Cell($xf, '');
					break;

				case XR_REC_MULRK:
					$row = $stream->getWord();
					$col = $stream->getWord();
					$count = ($stream->recUnreaded - 2)/6;
					for($i=0;$i<$count;$i++, $col++) {
						$xf = $stream->getWord();
						$this->rows[$row]->cells[$col] = new Cell($xf, $this->xl->XFormat($xf, $stream->getRK()));
					}
					break;

				case XR_REC_RK:
				case XR_REC_BLANK:
				case XR_REC_BOOLERR:
				case XR_REC_LABEL:
				case XR_REC_LABELSST:
				case XR_REC_NUMBER:
				case XR_REC_FORMULA:
					$info = $stream->readf('vrow/vcol/vxf');
					switch($code) {
						case XR_REC_RK:
							$value = $stream->getRK();
							break;
						case XR_REC_BLANK:
							$value = '';
							break;
						case XR_REC_BOOLERR:
							$value = $stream->getByte();
							$type = $stream->getByte();
							$value = $type ? self::$errValues[$value] : self::$boolValues[$value & 1];
							break;
						case XR_REC_LABEL:
							$value = $stream->getString();
							break;
						case XR_REC_LABELSST:
							$value = $xl->sst[$stream->getDword()];
							break;
						case XR_REC_NUMBER:
							$value = $stream->getDouble();
							break;
						case XR_REC_FORMULA:
							$result = $stream->read(8);
							list(, $s) = unpack('v', substr($result, 6));
							if ($s == 0xFFFF) {
								$type = unpack('c', $result);
								switch ($a) {
									case 0:
										$stream->getRec(XR_REC_STRING);
										$value = $stream->getString();
										break;
									case 1:
										list(, $value) = unpack('c', substr($result, 2));
										$value = self::$boolValues[$value];
										break;
									case 2:
										list(, $value) = unpack('c', substr($result, 2));
										$value = self::$errValues[$value];
										break;
									case
										$value = '';
									default:
										throw EApp('Undefined result');
								}
							}
							else
								list(, $value) = unpack('d', $result);
							break;
					}
					$this->rows[$info['row']]->cells[$info['col']] = new Cell($info['xf'], $xl->XFormat($info['xf'], $value));
					break;


				// not used
				case XR_REC_INDEX: // some writers put invalid information...
				case XR_REC_DBCELL: // some writers put invalid information...

				// GUI
				case XR_REC_SETUP:
				case XR_REC_PLV:
				case XR_REC_STANDARDWIDTH:

				case XR_REC_PRINTHEADERS:
				case XR_REC_PRINTGRIDLINES:
				case XR_REC_GRIDSET:
				case XR_REC_GUTS:
				case XR_REC_DEFAULTROWHEIGHT:
				case XR_REC_HEADER:
				case XR_REC_FOOTER:
				case XR_REC_HCENTER:
				case XR_REC_VCENTER:
				case XR_REC_LEFTMARGIN:
				case XR_REC_RIGHTMARGIN:
				case XR_REC_TOPMARGIN:
				case XR_REC_BOTTOMMARGIN:
				case XR_REC_PLS:
				case XR_REC_VERTICALPAGEBREAKS:
				case XR_REC_HEADERFOOTER:
				case XR_REC_DEFCOLWIDTH:

				case XR_REC_WINDOW2:
				case XR_REC_SELECTION:

				// calc
				case XR_REC_CALCCOUNT:
				case XR_REC_CALCMODE:
				case XR_REC_REFMODE:
				case XR_REC_ITERATION:
				case XR_REC_DELTA:
				case XR_REC_SAVERECALC:

				// info
				case XR_REC_FEATHEADR:
				case XR_REC_COLINFO:
				case XR_REC_WSBOOL:
				case XR_REC_DIMENSION:


					break;
				default:
					//printf("Uncatched in sheet %x".PHP_EOL, $r->code);
					//throw new EApp(sprintf("Uncatched in sheet %x", $r->code));
			}
			$code = $stream->getRec();
		}
	}
}

// @todo  Streamer
class XLStream {
	private $streamData;
	private $streamEnd;
	private $streamCursor;
	private $collation;
	private $handle;
	private $minifat;
	private $fat;
	private $recUnreaded;
	private $sectorSize;
	public $BIFF8;
	
	function __construct($filename, $collation = 'UTF-8') {
		$this->collation = $collation;
		
		# check permitions & open file
		if(!is_readable($filename)) throw new EApp('File can not be readable');
		if (!($f = $this->handle = fopen($filename, 'rb'))) throw new EApp('File can not be open');

		# read header sector, always 512 bytes
		$header = fread($f, 0x200);

		# check signature of excel file
		if (substr($header, 0, 8) != IDENTIFIER_OLE)  throw new EApp('File can not be parsed');

		# decode header information
		#
		# sector	- (2) size of sector in bits
		# minisector	- (2) size of minsector in miniFat bits
		# reserved	- (5x2)
		# csecFat	- (4) count sectors in FAT
		# sectDirStart	- (4) #sector of Directory
		# reserved	- (4)
		# miniSecCutOff - (4)
		# miniFatStart	- (4) start sector in Root Entry
		# miniFatLength - (4) count sectors in miniFat
		# fDIF		- (4) #sector of extended FAT
		# fcDIF		- (4) count sectors in extened FAT
		$this->head = unpack('vsector/vminisector/v5res/VcsectFat/VsectDirStart/Vres/VminiSecCutOff/VminiFatStart/VminiFatLength/VfDIF/vcDIF',
							 substr($header, 0x1E));

		# read first 109 sectors of GLOBAL FAT
		# GLOBAL FAT consist FAT & miniFAT
		$secfat = array_merge(unpack("V*", substr($header, 0x4C)), array());

		# init & calc size of sector ib n bytes
		$sz = $this->sectorShift = $this->head['sector'];
		$szSector = $this->sectorSize = 1 << $sz;

		# read addon sectors for GLOBAL FAT
		$secNo = $this->head['fDIF'];
		while ($secNo!=-2) {
			fseek($f, 0x200 + ($secNo << $sz));
			$secfat = array_merge($secfat, unpack("V*", fread($f, $szSector)));
			# last 4 bytes number is next sector
			$secNo = array_pop($secfat);
		}

		# read FAT
		$this->fat = array();
		$count = $this->head['csectFat'];
		$secNo = current($secfat);
		while ($count--) {
			fseek($f, 0x200 + ($secNo << $sz));
			$this->fat = array_merge($this->fat, unpack("V*", fread($f, $szSector)));
			$secNo = next($secfat);
		}

		# read miniFAT
		$this->minifat = unpack("V*", $this->readData($this->head['miniFatStart']));

		# read Directories
		$this->readDirectory();

		# read Directories
		$this->initStream();
	}
	
	function close() {
		fclose($this->handle);
	}
	
	function __destruct() {
		if ($this->handle) $this->close();
	}

	private function readData($secNo) {
		$data = '';
		while ($secNo != -2)  {
			fseek($this->handle, 0x200 + ($secNo << $this->sectorShift));
			$data .= fread($this->handle, $this->sectorSize);
			$secNo = $this->fat[$secNo];
		}
		return $data;
	}

	private function readDirectory() {
		$entry = str_split($this->readData($this->head['sectDirStart']), PROPERTY_STORAGE_BLOCK_SIZE);
		foreach($entry as $d) {
			list(, $nameSize, $type) = unpack('v*', substr($d, SIZE_OF_NAME_POS, 4));
			list(, $startBlock, $size) = unpack('V2', substr($d, START_BLOCK_POS, 8));
			$name = str_replace("\x00", "", substr($d, 0, $nameSize));
			if (($name == "Workbook") || ($name == "Book")) $this->wrkbook = array($startBlock, $size);
			elseif ($name == "Root Entry") $this->rootentry = array($startBlock, $size);
		}
		if (!$this->wrkbook && !$this->rootentry) throw new EApp('Cannot find workbooks or root directory');
	}

	private function initStream() {
		$this->streamData = '';
		$this->streamPos = 0;
		$this->streamCursor = 0;
		$this->recUnreaded = 0;

		# if size of work book is small
		if ($this->wrkbook[1] < SMALL_BLOCK_THRESHOLD){
			$rootdata = $this->readData($this->rootentry[0]);
			$block = $this->wrkbook[0];
			while ($block != -2) {
				$this->streamData .= substr($rootdata, $block << 6, SMALL_BLOCK_SIZE);
				$block = $this->minifat[$block];
			}
			$this->streamSize = strlen($this->streamData);
			$this->currentSector = -2;
		} else {
			$this->currentSector = $this->wrkbook[0];
			$this->streamSize = 0;
		}

		# first record always is BOF = Begin Of File
		$this->getRec(XR_REC_BOF);

		$version = $this->getWord();
		$this->BIFF8 = $version == XR_BIFF8;
		if (!$this->BIFF8 && $version != XR_BIFF5) throw new EApp('Not supported version');

		# check stream type;
		if ($this->getWord() != XR_WORKBOOKGLOBALS) throw new EApp('Not supported substream');
	}

	private function readDataBuf($size, $b = true) {
		if ($b) {
			if ($size > $this->recUnreaded) throw new EApp("No data in Rec[$size > {$this->recUnreaded}]");
			$this->recUnreaded -= $size;
		}

		# if size smaller than not readed space
		if ($this->streamSize - $this->streamPos >= $size) {
			$this->streamPos += $size;
			return substr($this->streamData, $this->streamPos - $size, $size);
		} else {
			$data = substr($this->streamData, $this->streamPos);  // ostatki
			$size -= ($this->streamSize - $this->streamPos);
			$this->streamCursor += $this->streamSize;

			$fullsectors  = $size >> $this->sectorShift;
			while ($this->currentSector!= -2 && $fullsectors) {
				fseek($this->handle, 0x200 + ($this->currentSector << $this->sectorShift));
				$data .= fread($this->handle, $this->sectorSize);
				$size -= $this->sectorSize;
				$this->streamCursor += $this->sectorSize;
				$this->currentSector = $this->fat[$this->currentSector];
				$fullsectors--;
			}
			if ($fullsectors || ($size && $this->currentSector== -2)) throw new EApp('No Data');
			$fullsectors = 32;

			$this->streamData = '';
			$this->streamSize = 0;

			while ($this->currentSector!= -2 && $fullsectors) {
				fseek($this->handle, 0x200 + ($this->currentSector << $this->sectorShift));
				$this->streamData .= fread($this->handle, $this->sectorSize);
				$this->streamSize += $this->sectorSize;
				$this->currentSector = $this->fat[$this->currentSector];
				$fullsectors--;
			}

			$this->streamPos = $size;
			return $data.substr($this->streamData, 0, $size);
		}
	}
	
	function seekDataBuf($pos) {
		if ($this->streamCursor <= $pos && $pos-$this->streamCursor < $this->streamSize) $this->streamPos = $pos - $this->streamCursor;
		else {
			$this->currentSector = $this->wrkbook[0];
			$s = $fullsectors = $pos >> $this->sectorShift;
			while ($fullsectors && $this->currentSector!=-2) {
				$this->currentSector = $this->fat[$this->currentSector];
				--$fullsectors;
			}
			if ($fullsectors || $this->currentSector==-2) throw new EApp('No Pos Data');
			$this->streamCursor = $s << $this->sectorShift;
			$this->streamPos = $pos - $this->streamCursor;
			// read first sector
			fseek($this->handle, 0x200 + ($this->currentSector << $this->sectorShift));
			$this->streamData = fread($this->handle, $this->sectorSize);
			$this->streamSize = $this->sectorSize;
			$this->currentSector = $this->fat[$this->currentSector];
		}
		$this->recUnreaded = 0;
	}

	public function read($size, $format = '') {
		$data = $this->readDataBuf($size);
		return $format ? $this->readf($format, $data) : $data;
	}

	public function readf($format, $limit = 0) {
		$sizes = array('V'=>4, 'v'=>2, 'd'=>8, 'c'=>1, 'C'=>1);
		$l = strlen($format);
		if ($l == 1) {
			list(, $v) = unpack($format, $this->readDataBuf($sizes[$format]));
			return $v;
		}
		elseif ($l==2 && $format[1]=='*') {
			return unpack($format, $limit ? $this->readDataBuf($sizes) : $this->readTail());
		}
		else {
			preg_match_all('"([A-Za-z])(?:[A-Za-z0-9]+)/?"', $format, $regs, PREG_PATTERN_ORDER);
			$size = 0;
			foreach($regs[1] as $f) $size += $sizes[$f];
			return unpack($format, $this->readDataBuf($size));
		}
	}
	
	function readTail($format = false) {
		$data = $this->readDataBuf($this->recUnreaded);
		return $format?unpack($format, $data):$data;
	}

	public function getRec($expect = false) {
		if ($this->recUnreaded) $this->skipRec();
		list(, $code, $length) = unpack('v2', $this->readDataBuf(4, false));
		if ($expect !== false) {
			if ($code != $expect) throw new EApp(sprintf("Unexpected %x but expect %x", $code, $expect));
		}
		$this->recUnreaded = $length;
		return $code;
	}

	public function skipRec() {
		$this->seekDataBuf($this->streamCursor + $this->streamPos + $this->recUnreaded);
		$this->recUnreaded = 0;
	}

	function getString($len=0) {
		if (!$this->BIFF8) {
			if (!$len) $len = $this->getByte();
			return $this->readDataBuf($len);
		}
		if (!$this->recUnreaded) $this->getRec(XR_REC_CONTINUE);
		if (!$len) $len = $this->getWord();
		$flag = $this->getByte();
		$compressed = !($flag & 1);

		$runs = $flag & 0x08 ? $this->getWord() : 0;
		$easize = $flag & 0x04 ? $this->getWord() : 0;

		$string = '';
		while ($len) {
			$bytes = $compressed?$len:$len<<1;
			if ($bytes > $this->recUnreaded) $bytes = $this->recUnreaded;

			$len -= $compressed ? $bytes : $bytes >> 1;
			$data = $this->readDataBuf($bytes);
			$string .= $compressed?$data:iconv('UTF-16LE', $this->collation, $data);
			if (!$len) break;
			$this->getRec(XR_REC_CONTINUE);
			$compressed = $this->getByte()==0;
		}

		if ($flag & 8) {
			//if ($runs*4>$phlen) throw new EApp("Continure REC3 ($flag)");
			$rt = str_split($this->readDataBuf($runs << 2), 4);
			foreach($rt as &$rti) $rti = unpack('v*', $rti);
		} else $rt = array();

		$rs = new RichString($string, $rt);

		// @todo decode east
		if ($flag & 4) {
			$rs->east = $this->readDataBuf($easize);
		}

		return new RichString($string, $rt);
	}

	function getString16($len) { return iconv('UTF-16LE', $colation, $this->readDataBuf($len << 1)); }
	function getDouble() { return $this->readf('d'); }
	function getDword() { return $this->readf('V'); }
	function getWord() { return $this->readf('v');	}
	function getByte() { return $this->readf('C'); }

	function getRK() {
		$rk = $this->stream->getDword();
		$num = $rk >> 2;
		if (!($rk &0x02)) {
			$exp = ($num >> 18) & 0x7ff;
			$num = (($rk&0x80000000)?-1:1) * (0x40000| ($num & 0x3ffff)) * pow(2, $exp - 1041);
		}
		if ($rk & 0x01) $num = (double)$num / 100.0;
		return $num;
	}
}

class XLReader {
	private $handle;
	public $sst;
	public $sheets;
	public $collation = 'UTF-8';
	
	// @todo private XF
	public $XF = array();
	public $Fonts = array();
	public $recUnreaded = 0;

	// Build-in formats
	var $Formats = array (
		0x0 => array("type"=> "generic", "format"=>"%s"),
		0x1 => array("type"=> "number", "format" =>"%1.0f"),     // "0"
		0x2 => array("type"=> "number", "format" =>"%1.2f"),     // "0.00",
		0x3 => array("type"=> "number", "format" =>"%1.0f"),     //"#,##0",
		0x4 => array("type"=> "number", "format" =>"%1.2f"),     //"#,##0.00",
		0x5 => array("type"=> "number", "format" =>"%1.0f"),     /*"$#,##0;($#,##0)",*/
		0x6 => array("type"=> "number", "format" =>'$%1.0f'),    /*"$#,##0;($#,##0)",*/
		0x7 => array("type"=> "number", "format" =>'$%1.2f'),    //"$#,##0.00;($#,##0.00)",
		0x8 => array("type"=> "number", "format" =>'$%1.2f'),    //"$#,##0.00;($#,##0.00)",
		0x9 => array("type"=> "number", "format" =>'%1.0f%%'),   // "0%"
		0xa => array("type"=> "number", "format" =>'%1.2f%%'),   // "0.00%"
		0xb => array("type"=> "number", "format" =>'%1.2f'),     // 0.00E00",
		0xe => array("type"=> "date", "format" =>"d/m/Y"),
		0xf => array("type"=> "date", "format" =>"d-M-Y"),
		0x10 => array("type"=> "date", "format" =>"d-M"),
		0x11 => array("type"=> "date", "format" =>"M-Y"),
		0x12 => array("type"=> "date", "format" =>"h:i a"),
		0x13 => array("type"=> "date", "format" =>"h:i:s a"),
		0x14 => array("type"=> "date", "format" =>"H:i"),
		0x15 => array("type"=> "date", "format" =>"H:i:s"),
		0x16 => array("type"=> "date", "format" =>"d/m/Y H:i"),
		0x25 => array("type"=> "number", "format" =>'%1.0f'),    // "#,##0;(#,##0)",
		0x26 => array("type"=> "number", "format" =>'%1.0f'),    //"#,##0;(#,##0)",
		0x27 => array("type"=> "number", "format" =>'%1.2f'),    //"#,##0.00;(#,##0.00)",
		0x28 => array("type"=> "number", "format" =>'%1.2f'),    //"#,##0.00;(#,##0.00)",
		0x29 => array("type"=> "number", "format" =>'%1.0f'),    //"#,##0;(#,##0)",
		0x2a => array("type"=> "number", "format" =>'$%1.0f'),   //"$#,##0;($#,##0)",
		0x2b => array("type"=> "number", "format" =>'%1.2f'),    //"#,##0.00;(#,##0.00)",
		0x2c => array("type"=> "number", "format" =>'$%1.2f'),   //"$#,##0.00;($#,##0.00)",
		0x2d => array("type"=> "date", "format" =>"i:s"),
		0x2e => array("type"=> "date", "format" =>"H:i:s"),
		0x2f => array("type"=> "date", "format" =>"i:s.S"),
		0x30 => array("type"=> "number", "format" =>'%1.0f'),     //"##0.0E0";
		0x31 => array("type"=> "generic", "format" =>'%s')
	);

	function __constructor() {
		$this->handle = false;
		$this->sheets = array();
	}

	function close() {
		$this->sheets = array();
	}

	function open($filename) {
		$s = $this->stream = new XLStream($filename, $this->collation);
		$fntCounter = 0;
		
		# record reading... while EOF record
		$code = $s->getRec();
		while ($code!=XR_REC_EOF) {
			switch ($code) {
							
				case XR_REC_BOUNDSHEET:
					$info = $s->readf('Voffset/voptions/cnamelen');
					# only visible & simple sheets, skip VB, dynamic & etc sheets
					if (($info['options'] & 4 != 0) || ($info['options'] & 0xFF00 != 0)) continue;

					$name = $s->getString($info['namelen']);
					$sh = $this->sheets[] = new XLSheet($this, $name);
					// @todo AAAAAAAA offset shit
					$sh->offset = $info['offset'];
					break;
					
				case XR_REC_INTERFACEHDR:
				case XR_REC_CODEPAGE:
					$this->codepage = $s->getWord();
					break;
					
				case XR_REC_FILEPASS:
					throw new EApp('File is crypted!');
				case XR_REC_NINETEENFOUR: // DATE MODE
					$this->nineteenFour = $s->getByte() == 1;
					break;
				case XR_REC_FORMAT:
					$code = $s->getWord();
					$this->Formats[$code] = array('type'=>"number", 'format'=>$s->getString());
					break;
				case XR_REC_XF:
					// @todo decode all params for GUI
					$xf = new XF($s);
					$this->XF[] = $xf;
					break;

				case XR_REC_XFCRC:
					// @todo EXTXF CRC
					break;

				case XR_REC_XFEXT:
					// @todo EXTXF
					break;

				case XR_REC_STYLEEXT:
					// @todo read Style EXT
					break;

				case XR_REC_TABLESTYLES:

					// @todo read Table Style
					$s->read(12); // skip 12 bytes
					$count = $s->getDWord();
					$lenDStyle = $s->getWord();
					$lenDPivot = $s->getWord();
					//$this->defTableStyleName = $s->getString16($lenDStyle);
					//$this->defPivotName = $s->getString16($lenDPivot);
					/*for ($i=0; $i<$count; $i++) {
						$t = $s->getRec(XR_REC_TABLESTYLE);
						$this->readDataBuf(12); // skip 12 bytes
						$ts = new TableStyle();
						$ts->type = $s->getWord();
						$elCount = $s->getWord();
						$this->TableStyle[$s->getString()] = $ts;
						for ($e=0; $e<$elCount; $e++) {
							$e = $s->getRec(XR_REC_TABLESTYLEELEMENT);
							$this->readDataBuf(12);
							$el = new TableStyleElement;
							$el->type = $s->getDword();
							$el->size = $s->getDword();
							$el->DXFindex = $s->getDword();
							$ts->elements[] = $el;
						}
					}*/
					break;
				/*
				 tseWholeTable 0 Applies to whole table
tseHeaderRow 1 Header row formatting
tseTotalRow 2 Total row formatting
tseFirstColumn 3 First column formatting
tseLastColumn 4 Last column formatting
tseRowStripe1 5 First row stripe formatting
tseRowStripe2 6 Second row stripe formatting
tseColumnStripe1 7 First column stripe formatting
tseColumnStripe2 8 Second column stripe formatting
tseFirstHeaderCell 9 First cell of header row formatting
tseLastHeaderCell 10 Last cell of header row formatting
tseFirstTotalCell 11 First cell of total row formatting
tseLastTotalCell 12 Last cell of total row formatting
tseSubtotalColumn1 13 Top level subtotals column formatting
tseSubtotalColumn2 14 Alternating even column subtotals formatting
tseSubtotalColumn3 15 Alternating odd column subtotals formatting
tseSubtotalRow1 16 Top level subtotals row formatting
tseSubtotalRow2 17 Alternating even subtotals row formatting
tseSubtotalRow3 18 Alternating odd subtotals row formatting
tseBlankRow 19 Blank row formatting
tseColumnSubheading1 20 Top level column subheading formatting
tseColumnSubheading2 21 Alternating even column subheading formatting
tseColumnSubheading3 22 Alternating odd column subheading formatting
tseRowSubheading1 23 Top level row subheading formatting
tseRowSubheading2 24 Alternating even row subheading formatting
tseRowSubheading3 25 Alternating odd row subheading formatting
tsePageFieldLabels 26 Page field label formatting
tsePageFieldValues 27 Page field values formatting*/

				// @todo What is style?
				case XR_REC_STYLE:
					$ixf = $s->getWord();
					$xf = $this->XF[$ixf & 0xFFF];
					if ($ixf & 0x8000) {
						/*=00h Normal =01h RowLevel_n =02h ColLevel_n =03h Comma =04h Currency =05h Percent =06h Comma[0] =07h Currency[0] */
						$xf->BuiltInStyle = $s->getByte();
						$xf->StyleLEvel = $s->getByte();
					} else {
						$xf->StyleName = $s->getString($s->BIFF8?0:$s->getByte());
					}
					break;

				case XR_REC_SST:
					$total = $s->getDWord();
					$uniq = $s->getDWord();
					for($ind=0; $ind<$uniq; $ind++) {
						$this->sst[$ind] = $s->getString();
					}
					break;

				case XR_REC_WRITEACCESS:
					$this->UserName = $s->getString($s->BIFF8?0:$s->getWord());
					break;

				case XR_REC_TABID:
					$this->TabIndex = $s->readf('v*');
					break;

				case XR_REC_PRECISION:
					$this->precision = $s->getWord();
					break;

				case XR_REC_REFRESHALL:
					$this->refreshAll = $s->getWord();
					break;

				// @todo check & throw
				case XR_REC_PROTECT:
					$this->protect = $s->getByte();
					break;

				case XR_REC_PASSWORD:
					$this->hashPassword = $s->getWord();
					break;
					/* @todo Need us passwored?
				ALGORITHM Get_Password_Hash( password )
1) hash = 0 ;
   char_index = char_count = character count of password
2) char_index = char_index - 1
3) char = character from password with index char_index {0 is leftmost character}
4) hash = hash XOR char
5) rotate the lower 15 bits of hash left by 1 bit
6) IF char_index > 0 THEN JUMP 2)
7) RETURN hash XOR char_count XOR CE4Bh
					*/

				case XR_REC_FORCEFULLCALCULATION:
					$s->read(12); // skip 12 bytes
					$this->forceFullCalculation = $s->getDword();
					break;

				case XR_REC_BOOKEXT:
					// @todo Check flags

				case XR_REC_FONT:
					$this->Fonts[$fntCounter++] = new Font($s);
					if ($fntCounter==4) $fntCounter++;
					break;

				// not used
				case XR_REC_COUNTRY:
				case XR_REC_COMPRESSPICTURES:
				case XR_REC_COMPAT12:
				case XR_REC_EXTSST:
				case XR_REC_MTRSETTINGS:
				case XR_REC_FNGROUPCOUNT:
				case XR_REC_USESELFS:
				case XR_REC_BOOKBOOL:
				case XR_REC_BACKUP:
				case XR_REC_PROT4REV:
				case XR_REC_PROT4REVPASS:
				case XR_REC_WINDOWPROTECT:
				case XR_REC_INTERFACEEND:
				case XR_REC_DSF:
				case XR_REC_RECALCID:
				case XR_REC_EXCEL9FILE:

				// @todo GUI
				case XR_REC_THEME:
				case XR_REC_HIDEOBJ:
				case XR_REC_MMS:
				case XR_REC_WINDOW1:
				case XR_REC_PALETTE:
				case XR_REC_CLRTCLIENT:
					break;
				default:
					//printf("Uncatched %x %d size".PHP_EOL, $r->code, $r->length);
					//throw new EApp(sprintf("Uncatched %x %d size in Global".PHP_EOL, $r->code, $r->length));
					//$this->skipRec();
			}
			$code = $s->getRec();
		}

		# read Sheet directory
		foreach ($this->sheets as $sheet) {
			$s->seekDataBuf($sheet->offset);
			$sheet->load($s);
		}
	}

	function fontByFormat($xf) {
		return $this->Fonts[$this->XF[$xf]->font];
	}

	function XFormat($xf, $value) {
		$xf = $this->XF[$xf];
		$format = $this->Formats[$xf->format];
		switch ($format['type']) {
			case 'date':
				return date($format['format'], $value);
			case 'number':
				return $value;
				return sprintf($format['format'], $value);
			default:
				return $value;
		}
	}

	function createDate($numValue)	{
		if ($numValue > 1) {
			$utcDays = $numValue - ($this->nineteenFour ? XR_UTCOFFSETDAYS1904 : XR_UTCOFFSETDAYS);
			$utcValue = round(($utcDays+1) * XR_MSINADAY);
			$string = date ($this->curformat, $utcValue);
			$raw = $utcValue;
		} else {
			$raw = $numValue;
			$hours = floor($numValue * 24);
			$mins = floor($numValue * 24 * 60) - $hours * 60;
			$secs = floor($numValue * XR_MSINADAY) - $hours * 60 * 60 - $mins * 60;
			$string = date ($this->curformat, mktime($hours, $mins, $secs));
		}
		return array($string, $raw);
	}
}
?>