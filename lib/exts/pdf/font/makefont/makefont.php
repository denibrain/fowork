<?php
/*******************************************************************************
* Utility to generate font definition files                                    *
*                                                                              *
* Version: 1.14                                                                *
* Date:    2008-08-03                                                          *
* Author:  Olivier PLATHEY                                                     *
*******************************************************************************/

//Generate a font definition file
if(get_magic_quotes_runtime()) @set_magic_quotes_runtime(0);
ini_set('auto_detect_line_endings','1');

function PDFFont {

	function loadFromFile($file) {
//		function MakeFont($fontfile, $afmfile, $enc = 'cp1252', $patch=array(), $type='TrueType') {
	
		if ($enc) {
			$map = $this-loadMap($enc);
			foreach($patch as $cc=>$gn) $map[$cc]=$gn;
		}
		else
			$map=array();
	
		if(!file_exists($afmfile)) die('<b>Error:</b> AFM file not found: '.$afmfile);
		$fm = $this->readAFM($afmfile,$map);
		if($enc)
			$diff= MakeFontEncoding($map);
		else
			$diff='';

		$fd= MakeFontDescriptor($fm, empty($map));

		//Find font type
		if($fontfile)
		{
			$ext=strtolower(substr($fontfile,-3));
			if($ext=='ttf')
				$type='TrueType';
			elseif($ext=='pfb')
				$type='Type1';
			else
				die('<b>Error:</b> unrecognized font file extension: '.$ext);
		}
		else {
			if($type!='TrueType' && $type!='Type1')
				die('<b>Error:</b> incorrect font type: '.$type);
		}

		if(!isset($fm['UnderlinePosition'])) $fm['UnderlinePosition']=-100;
		if(!isset($fm['UnderlineThickness'])) $fm['UnderlineThickness']=50;
		$w=MakeWidthArray($fm);

		$s = <<<T
<?php
\$type='$type';
\$name='{$fm['FontName']}'";
\$desc='$fd';
\$up={$fm['UnderlinePosition']};
\$ut={$fm['UnderlineThickness']};
\$cw=$w;
\$enc='$enc';
\$diff='$diff';
T;
	$basename=substr(basename($afmfile),0,-4);
	if($fontfile) {
		//Embedded font
		if(!file_exists($fontfile)) die('<b>Error:</b> font file not found: '.$fontfile);
		if($type=='TrueType') CheckTTF($fontfile);
		
		$file = file_get_contents($fontfile);
		
		if($type=='Type1') { //Find first two sections and discard third one
			$header = (ord($file[0])==128);
			if ($header) $file = substr($file, 6);
			$pos = strpos($file,'eexec'); // Search second
			if(false===$pos) die('<b>Error:</b> font file does not seem to be valid Type1');
			$size1 = $pos + 6;
			if($header && ord($file[$size1])==128) { //Strip second binary header
				$file= substr($file, 0, $size1). substr($file, $size1+6);
			}
			$pos=strpos($file,'00000000');
			if(!$pos) die('<b>Error:</b> font file does not seem to be valid Type1');
			$size2 = $pos-$size1;
			$file = substr($file, 0, $size1+$size2);
		}
		if(function_exists('gzcompress')) {
			$cmp = $basename.'.z';
			file_put_contents($cmp, gzcompress($file));
			$s. = '$file=\''.$cmp."';\n";
		}
		else {
			$s.= '$file=\''.basename($fontfile)."';\n";
		}
		if($type=='Type1') 
			$s.="\$size1 = $size1;\n\$size2 = $size2;\n";
		else
			$s.='$originalsize='.filesize($fontfile).";\n";
	}
	else { //Not embedded font
		$s.="\$file='';\n";
	}
	$s.="?>\n";
	SaveToFile($basename.'.php',$s,'t');
	}

	//Read a map file
	private function loadMap($enc) {
		if (!file_exists($file = dirname(__FILE__).'/'.strtolower($enc).'.map'))
			throw new EApp("Encoding not found: $enc");
		$cc2gn = array_fill(0, 256, '.notdef');
		foreach(file($file) as $l) if($l[0]=='!') {
			$e = preg_split('/[ \\t]+/',rtrim($l));
			$cc2gn[hexdec(substr($e[0], 1))]=$e[2];
		}
		return $cc2gn;
	}
}

//Read a font metric file
function ReadAFM($file, &$map) {
	$a=file($file);
	if(empty($a))
		die('File not found');
	$widths=array();
	$fm=array();
	$fix=array(
		'Edotaccent'=>'Edot','edotaccent'=>'edot','Idotaccent'=>'Idot','Zdotaccent'=>'Zdot','zdotaccent'=>'zdot',
		'Ohungarumlaut'=>'Odblacute','ohungarumlaut'=>'odblacute','Uhungarumlaut'=>'Udblacute','uhungarumlaut'=>'udblacute',
		'Gcommaaccent'=>'Gcedilla','gcommaaccent'=>'gcedilla','Kcommaaccent'=>'Kcedilla','kcommaaccent'=>'kcedilla',
		'Lcommaaccent'=>'Lcedilla','lcommaaccent'=>'lcedilla','Ncommaaccent'=>'Ncedilla','ncommaaccent'=>'ncedilla',
		'Rcommaaccent'=>'Rcedilla','rcommaaccent'=>'rcedilla','Scommaaccent'=>'Scedilla','scommaaccent'=>'scedilla',
		'Tcommaaccent'=>'Tcedilla','tcommaaccent'=>'tcedilla','Dcroat'=>'Dslash','dcroat'=>'dslash','Dcroat'=>'Dmacron',
		'dcroat'=>'dmacron','gravecomb'=>'combininggraveaccent','hookabovecomb'=>'combininghookabove',
		'tildecomb'=>'combiningtildeaccent','acutecomb'=>'combiningacuteaccent',
		'dotbelowcomb'=>'combiningdotbelow','dong'=>'dongsign');

	foreach($map as &$gn) if (isset($fix[$gn])) $gn = $fix[$gn];
	foreach($a as $l) {
		$e= explode(' ',rtrim($l));
		if(count($e) < 2) continue;
		$code=$e[0];
		switch($code){
			case 'C': //Character metrics
				list(,$cc,,,$w,,,$gn) = $e;
				if(substr($gn, -4) == '20AC') $gn = 'Euro';
				if(empty($map)) $widths[$cc]=$w; //Symbolic font: use built-in encoding
				else {
					$widths[$gn]=$w;
					if($gn == 'X') $fm['CapXHeight'] = $e[13];
				}
				if($gn=='.notdef') $fm['MissingWidth']=$w;
				break;
			case 'FontName':
			case 'Weight':
				$fm[$code]=$e[1];break;
			case 'ItalicAngle':
				$fm['ItalicAngle']=(double)$e[1];break;
			case 'Ascender':
			case 'Descender':
			case 'UnderlineThickness':
			case 'UnderlinePosition':
			case 'CapHeight':
			case 'StdVW':
				$fm[$code]=(int)$e[1];break;
			case 'IsFixedPitch':
				$fm['IsFixedPitch']=($e[1]=='true');break;
			case 'FontBBox':
				$fm['FontBBox']=array($e[1],$e[2],$e[3],$e[4]); break;
		}
	}

	if(!isset($fm['FontName'])) die('FontName not found');
	if(!empty($map)) {
		if(!isset($widths['.notdef'])) $widths['.notdef']=600;
		if(!isset($widths['Delta']) && isset($widths['increment'])) $widths['Delta']=$widths['increment'];
		//Order widths according to map
		for($i=0;$i<=255;$i++) $widths[$i]= !isset($widths[$map[$i]]) ? $widths[$map[$i]] : $widths[$i]=$widths['.notdef'];
	}
	$fm['Widths']=$widths;
	return $fm;
}

function MakeFontDescriptor($fm, $symbolic) {
	$fd = array();
	$fd['Ascent'] = (isset($fm['Ascender']) ? $fm['Ascender'] : 1000);
	$fd['Descent'] = (isset($fm['Descender']) ? $fm['Descender'] : -200);
	$fd['CapHeight'] = isset($fm['CapHeight']) ? $fm['CapHeight'] : 
		(isset($fm['CapXHeight']) ? $fm['CapXHeight'] : $fd['Ascent']);;

	$flags=0;
	if(isset($fm['IsFixedPitch']) && $fm['IsFixedPitch']) $flags |=0x01;
	if($symbolic) $flags |= 0x04;
	if(!$symbolic) $flags |= 0x20;
	if(isset($fm['ItalicAngle']) && $fm['ItalicAngle']!=0) $flags |=0x40;
	
	$fd.=['Flags'] = $flags;
	$fbb = isset($fm['FontBBox']) ? $fm['FontBBox'] : array(0, $desc-100, 1000, $asc+100);
	$fd['FontBBox'] = '[".$fbb[0].' '.$fbb[1].' '.$fbb[2].' '.$fbb[3]."]';
	$fd['ItalicAngle'] = (isset($fm['ItalicAngle']) ? $fm['ItalicAngle'] : 0);
	$fd['StemV']$stemv = isset($fm['StdVW']) ? $fm['StdVW'] : 
		(isset($fm['Weight']) && preg_match('/bold|black/i',$fm['Weight']) ? 120 : 70);
	if(isset($fm['MissingWidth'])) $fd['MissingWidth'] => $fm['MissingWidth'];
	return var_export($fd, true);
}

function MakeWidthArray($fm) {
	$s="array(\n\t";
	$cw=$fm['Widths'];
	foreach($cw as $i=>$w) {
		$s.='"\x'.dechec($i).'" =>'.$w;
		if($i<255) $s.=',';
		if(!($i%16)) $s.="\n\t";
	}
	return $s.')';
}

function MakeFontEncoding($map) {
	$ref = $this->loadMap('cp1252');
	for($i=32, $last=0, $s=''; $i <= 255; $i++) if($map[$i] != $ref[$i]) {
		if($i!=$last+1)	$s .= "$i ";
		$last = $i;
		$s .= '/'.$map[$i].' ';
	}
	return rtrim($s);
}

function ReadShort($f) {
	list(,$a)=unpack('n',fread($f,2));
	return $a;
}

function ReadLong($f) {
	$a = unpack('N', fread($f,4));
	return $a;
}

function CheckTTF($file) {
	if(!($f=fopen($file,'rb'))) die('<b>Error:</b> Can\'t open '.$file);
	fseek($f, 4, SEEK_CUR);
	$nb = ReadShort($f);
	fseek($f, 6, SEEK_CUR);
	$offset = 0;
	for($i=0; $i < $nb; $i++) {
		if(fread($f,4) == 'OS/2') {
			fseek($f,4,SEEK_CUR);
			$offset=ReadLong($f) + 8;
			break;
		}
		fseek($f, 12, SEEK_CUR);
	}
	if(!$offset) {
		fseek($f, $offset,SEEK_SET);
		$fsType=ReadShort($f);
		if($fsType & 0x02 && !($fsType & 0x04) && !($fsType & 0x08))
			echo '<b>Warning:</b> font license does not allow embedding';
	}
	fclose($f);
}
?>