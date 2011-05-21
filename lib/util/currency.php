<?php
namespace FW\Util;

class Currency extends \FW\Object {
	const Unknown = 1;
	const RUB = 1;
	const USD = 2;
	
	private $amount;
	private $currency;

	function __construct($amount, $currency = Currency::RUB) {
		$this->amount = $amount;
		$this->currency = $currency;
	}
	
	function __get($key) {
		switch($key) {
			case 'inWords': return $this->inWords();
		}
	}
	
	function inWords() {
		$valutes = array(array('', '', ''), array('�����','�����','������'), array('������','�������','��������'));
		$z = array(0=>$valutes[$this->currency], 1 => array('������','������','�����'), 2 => array('�������','��������','���������'), 3 => array('��������','���������','����������'));
		$i = $this->amount;
		if (!$i) return trim('���� '.$z[0][2]);
		list($i, $frac) = explode('.', $i);
		$i = str_split(strrev((string)((int)$i)), 3);
		$hl = array(1=>'���', '������', '������', '���������', '�������', '��������', '�������', '���������','���������');
		$e1 = array('������', '����������', '����������', '����������', '������������', '�����������', '�����������', '�����������', '�������������', '�������������');
		$el = array('', '����', '���', '���', '������', '����', '�����', '����', '������', '������');
		$e2 = array('', '����', '���', '���', '������', '����', '�����', '����', '������', '������');
		$dl = array(2=>'��������', '��������', '�����', '���������', '����������', '���������', '�����������', '���������');
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
			$fr = array(array('', '', ''), array('�������','�������','������'), array('����','�����','������'));
			array_unshift($i, sprintf('%02', $frac).' '.$fr[$k] [ ($e=$frac % 10)>4||$e==0||($frac>10&&$frac<20) ? 2: ($e==1?0:1) ]);
		}
		return trim(ucfirst(implode(' ', array_reverse($i))));
	}	
}
?>