<?php
namespace FW\Text;

class Cases {
	const NAME = 1;
	const FNAME = 2;
	const SNAME = 3;
	
	const NOMINATIVE = 1;
	const GENITIVE = 2;
	const dative = 3;
	const accusative = 4;
	const ablative = 5;
	const prepositional = 6;
	
	

	function CasesC($word, $case = Cases::GENITIVE, $male="*", $type) {
		if ("" == $word)
			return '';
		if (false!==($pos = strpos($word, '-')))
			return CasesC(substr($word, 0, $pos)).'-'.CasesC(substr($word, $pos + 1));
		
		$word = ucfirst(strtolower($word));
		
		$xyz = substr($word, -3);
		$yz = substr($xyz, 1);
		$x = substr($xyz, 0, 1);
		$z = substr($yz, 1);
		
		if (
			$case ==1 ||
			($type == Cases::SNAME && $male == "û") ||
			($type == Cases::NAME && preg_match("/ìıÿ|[ìëê]èÿ|æàÿ|ëåÿ/", $xyz)) ||
			($type == Cases::FNAME && preg_match("/[îèåó".($male=="÷" ? "":"áâãäæçêëìíïğñòôõö÷øùú")."]/",$z))
		)
		return $word;
	
		$zd = $yz == 'èÿ' ? 5 : (false === ($pos = strpos("àéÿü", $z)) ? 0 : $pos + 1);
		$len = strlen($word);
	  
		if ($zd == 4 && $male == "÷") $zd = 2;
		elseif (!$type == Cases::NAME) $zd = $zd;
		elseif (preg_match("/([îåèóş]|[èû]õ|[àå¸èîóûışÿ]à)$/",$yz)) $zd = 9;
		elseif ($male != "÷") {
			if ($yz == 'àÿ') $zd = 7;
			elseif ($z!= "à") $zd = 9;
			elseif (preg_match("/[ïäöøáòãê]à/", $yz)) $zd = 1;
			else $zd = 6;
		}
		elseif (
				(preg_match("/[îû]é/",$yz) && $len > 4 && (substr($word, -4) != "îïîé")) ||
				(preg_match('/[æíãõê÷øù]/', $x) && $yz=='èé') // èé
		) $zd = 8;
	  
		if ($zd == 9)
			return $word;
	
		if ($zd == 8 && $case !=5) {
			if (preg_match('/[÷øù]/', $x) || preg_match("/[æí]èé/", $xyz))
				$zf = "å";
			else
				$zf = "î";
		}
		elseif ($word=="Ëåâ") $zf = "üâ";
		elseif (preg_match("[^àå¸èéîóışÿ]", $word[$len - 4]) && preg_match('/[^àå¸èéîóışÿæ]/', $x) && $xyz != 'ûåö') $zf = "";
		elseif ($yz=='åë') $zf = "ë";
		elseif ($yz=='îê') $zf = "ê";
		elseif ($yz=='ÿö') $zf = "éö";
		elseif ($xyz == 'áåğ') $zf = "ğ";
		elseif ($xyz == 'ëåö') $zf = "üö";
		elseif (preg_match("/[âá]åé/", $xyz)) $zf = "ü";
		elseif (preg_match("/[äïìíğâ]åö/", $xyz, $reg)) $zf = "ö";
		elseif (preg_match("/[àèû]åö/", $xyz, $reg)) $zf = "éö";
		else  $zf = "";
	
//		echo "\nformula = ".(2 * (5 * $zd + $case) - 3)."[zd=$zd, zf=$zf]\n";
	
		$t1 = substr("îûûå", false ===($pos = strpos("âí÷",$z)) ? 0 : $pos + 1, 1) . "ì";
		$t2 = preg_match("/^[ãæêõø]/", $yz) ? "è " : "û ";
		$t3 = $yz=='èé' ? "è " : "å ";
		$t4 = (($zf == "å") || ($yz=='èé') || (preg_match("/[ãõê]/", $x)) ? "è" : "û")."ì";
	
		  $zf= substr($word, 0, $len - ($zd > 6 || $zf != "" ? 2 : ($zd > 0 ? 1 : 0)))  . $zf .
			   rtrim(substr(
			// 100 - 3*4 = 88, 88/2 = 44
	"à ó à {$t1}å {$t2}å ó îéå ÿ ş ÿ åì{$t3}è å ş åéå è è ü üşè è è ş åéè îéîéó îéîéîéîéóşîéîéãîìóãî{$t4}"
					,
					2 * (5 * $zd + $case - 2), 2));
		return  $zf;
	}
 
	function format($fio, $case = Cases::GENITIVE, $format = "%n %f %s") {
		if (!preg_match('/([à-ÿÀ-ß¸¨-]+)\s+([à-ÿÀ-ß¸¨-]+)(?:\s+([à-ÿÀ-ß¸¨-]+)(:?\s+(îãëû|êûçû))?)?$/i', trim($fio), $reg))
			throw Exception("Invalid FIO");
		if (isset($reg[4]))
			$male = strtolower($reg[4]) == 'îãëû' ? 'û':'û';
		elseif (isset($reg[3]))
			$male = substr($reg[3], -1);
		else
			$male = '*';
		$self = $this;
		return preg_replace_callback('/%[nfs]/i', function ($matches) use ($reg, $male, $case, $self) {
			$i = array('n'=>1, 'f'=>2, 's'=>3, 'N'=>1, 'F'=>2, 'S'=>3);
			$k = $matches[0][1];
			$type = $i[$k];
			$suff = '';
			switch ($k) {
				case "S":
					if (!isset($reg[$type])) return '';
				case "N":
				case "F":
					return strtoupper($reg[$type][0]).'.';
				case "s":
					if (isset($reg[4])) $suff = ' '.$reg[4];
				case "n":
				case "f":
					return $self->CasesC($reg[$type], $case, $male, $type).$suff;
			}
		}, $format);
	}
	
	function format2($str, $case = Cases::GENITIVE, $z3 = 0) {
	  $str = trim($str);
	  $pos =strpos($str." ", " ") + 1;
	  $z5=substr($str, 0, $pos - 1);
	  $z6=substr($z5, -2);
	  $tail = substr($str, $pos);
	  $z7 = preg_match("/[àèû]é$/", $z5) && !preg_match("/(?:şùèé|ííûé)$/", $z5) && $z3==0 ? "1" : "*";
	  
		if (preg_match('/àÿ?$/', $z5))
			$z = $this->CasesC($z5, $case, $z7, 1)." ".$this->CasesC($tail, $case);
		else
			$z = $this->CasesC($z5, $case, "÷", 1).
				($z6=="èé" && strpos($str," ")===false ?
					"" : " ".($z7=="1" ? $this->format2($tail, $case, 1) : $tail));
				
	  return trim(strtolower($z));	
	}	
}

