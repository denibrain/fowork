<?php
namespace FW\Util;

/* only 1970 - 20 */

define("DTI_1",    365);
define("DTI_4",    DTI_1   * 4 + 1);
define("DTI_100",  DTI_4  * 25 - 1);
define("DTI_400",  DTI_100 * 4 + 1);
define("DTI_3200", DTI_400 * 8 - 1); 

define("DTI_DAY",    1);
define("DTI_YEAR",   2);
define("DTI_MONTH",  3);


class DateTime extends \FW\Object {
	
	static public $monthLength = Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	static public $monthOffset = Array(0, 31, 59, 90, 120, 151, 181, 212, 243, 273, 304, 334);
	
	private $timestamp;
	private $day;    // 0..30(29,28,27)
	private $month;  // 0..11
	private $year;   // 0..2999
	private $monthSize; // 28..31
	private $hour;   // 0..23
	private $minute; // 0..59
	private $second; // 0..59
	
	private $bissextile;
	
	private $days;   // 0..? days
	

	static function getmicrotime() { list($usec, $sec) = explode(" ",microtime()); return ((float)$usec + (float)$sec); }
	
	function __construct($init = false) {
		if ($init === false) $init = time();
		if (is_int($init)) {
			$d = getdate($init);
			$this->day = $d['mday'] - 1;
			$this->setYear($d['year']);
			$this->setMonth($d['mon'] - 1);
			$this->hour = $d['hours'];
			$this->minute = $d['minutes'];
			$this->second = $d['seconds'];
		} else {
			$d = date_parse((string)$init);
			$this->day = $d['day'] - 1;
			$this->setYear($d['year']);
			$this->setMonth($d['month'] - 1);
			$this->hour = $d['hour'];
			$this->minute = $d['minute'];
			$this->second = $d['second'];
		}
		
		$this->days = false;
	}
	
	static function yearToDays($year) {
		$d2 =  DTI_400 * (int)($year / 400); $year %= 400;
		$d2 += DTI_100 * (int)($year / 100); $year %= 100;
		$d2 += DTI_4 * ($year >> 2);   $year &= 3;
		$d2 += DTI_1 * $year;
		return $d2;
	}
	
	// TODO complete body of setByFormat
	function setByFormat($date, $format = 'Y-m-d H:i:s') {
		if (is_string($init)) {
			$parse = date_parse_from_format($format, $date);
		}
	}
	
	function __get($key) {
		switch ($key) {
			case 'day': return $this->day + 1;
			case 'monthSize': return $this->monthSize;


			case 'month': return $this->month + 1;
			case 'year': return $this->year;
			case 'hour': return $this->hour;
			case 'minute': return $this->minute;
			case 'second': return $this->second;
			case 'date': return sprintf("%04d-%02d-%02d", $this->year, $this->month + 1, $this->day + 1);
			case 'days': return $this->days === false ? $this->days = $this->getDays() : $this->days;
			case 'timestamp': return $this->timestamp;
			default:
				return parent::__get($key);
		}
	}
	
	function __set($key, $value) {
		switch ($key) {
			case 'day': return $this->setDay($value - 1);
			case 'month': return $this->setMonth($value - 1);
			case 'year': return $this->setYear($value);
			case 'hour': return $this->setHour($value);
			case 'minute': return $this->setMinute($value);
			case 'second': return $this->setSecond($value);
			default:
				return parent::__set($key, $value);
		}
		
	}
	
	private function getDays() {
		return self::yearToDays($this->year) + $this->day + self::$monthOffset[$this->month];		
	}
	
	function addInterval($int, $step) {
		if (!$int) return $this;

		if ($step == DTI_YEAR) {
			$this->days = false;
			$this->setYear($this->year + $int);
		}
		elseif ($step == DTI_MONTH) {
			$this->days = false;
			$int += $this->month;
			if ($int < 0) throw new \Exception("Result month is negative");
			if ($v = $int / 12) $this->setYear($this->year + $v);
			$this->setMonth($int % 12);
		}
		if ($step == DTI_DAY) {
			$start = $this->__get('days');
			
			if ($this->month > 1) $start += (int)$this->bissextile;
			$this->days = $start += $int;
			if ($start < 0) throw new \Exception("Result is negative");
			
			$y = 0;
			$a = array(400=>DTI_400, 100=>DTI_100, 4=>DTI_4, 1=>DTI_1);
			foreach($a as $k => $size)
			if ($start >= $size) {
				$y += $k * (int)($start / $size);
				$start %= $size;
			}

			$this->setYear($y);
			if (self::$monthOffset[1] > $start)
				$m = 0;
			elseif (self::$monthOffset[2] + (int)$this->bissextile > $start) {
				$m = 1;
			}
			else {
				$start -= (int)$this->bissextile;
				for ($m = 3; $m < 12; $m++)
					if (self::$monthOffset[$m] > $start) {
						break;
					}
				--$m;
			}
			$this->setMonth($m);
			$start -= self::$monthOffset[$this->month];

			$this->setDay($start);
		}
		return $this;
	}

	function interval($sub, $unit) {
		if ($unit == DTI_DAY) return $this->__get('days') - $sub->__get('days');
		else
		if ($unit == DTI_YEAR) return $this->year - $sub->year;
		else
		if ($unit == DTI_MONTH) return ($this->year - $sub->year) * 12 + $this->month - $sub->month;
		
		return 0;
	}
	
	private function setDay($value) {
		if ($value >= $this->monthSize || $value < 0) throw new \Exception("Invalid day value $value");
		$this->day = $value;
	}
	private function setMonth($value) {
		if ($value > 11 || $value < 0) throw new \Exception("Invalid month value");
		$this->month = $value;
		if ($this->month == 1 && $this->bissextile) $this->monthSize = 29;
		else $this->monthSize = self::$monthLength[$this->month];
		
		if ($this->day >= $this->monthSize) $this->day = $this->monthSize - 1;
	}
	
	private function setYear($value) {
		if ($value > 2999 || $value < 0) throw new \Exception("Invalid year value");
		$this->year = $value;
		$this->bissextile = ($this->year % 4 ==0) && ($this->year % 100 != 0 || $this->year % 400 == 0);
	}
	
	
	private function setHour($value) {
		
	}
	private function setMinute($value) {
		
	}
	private function setSecond($value) {
		
	}
	
	function datef($format, $timestamp = 0) {
		if (!$timestamp) $timestamp = time();
		if (preg_match('/[DlLFMS]/', $format, $regs)) {
			$date = getdate($timestamp);
			$F = array(1 => "€нварь", "февраль", "март", "апрель",	"май", "июнь",
				"июль", "август", "сент€брь", "окт€брь", "но€брь", "декабрь");
			$S = array(1 => "€нвар€", "феврал€", "марта", "апрел€", "ма€", "июн€",
				"июл€", "августа", "сент€бр€", "окт€бр€", "но€бр€",  "декабр€");
			$M = array(1 =>"€нв", "фев", "мар", "апр", "май", "июн", "июл", "авг",
				"сен", "окт", "но€", "дек");
			$D = array("вс", "пн", "вт", "ср", "чт", "пт", "сб");
			$l = array("воскресенье", "понедельник", "вторник", "среда", "четверг", "п€тница", "суббота");
			$L = array("¬оскресенье", "ѕонедельник", "¬торник", "—реда", "„етверг", "ѕ€тница", "—уббота");
			$hash = array('F'=>'mday', 'S'=>'mday', 'M'=>'mday', 'D'=>'wday', 'l'=>'wday', 'L'=>'wday');
			foreach($regs as $v) $rep[] = $$v[$date[$hash[$v]]];
			$format = str_replace($regs, $rep, $format);
		}
		return date($format, $timestamp);
	}
	
	function __toString() {
		return sprintf("%04d-%02d-%02d %02d:%02d:%02d",
			$this->year, $this->month + 1, $this->day + 1,
			$this->hour, $this->minute, $this->second);
	}
}
?>