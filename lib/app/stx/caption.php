<?php
$this->terms = array(
	'var'	=>'\$[$0-9]',
	'start'	=>'{',
	'fin'	=>'}',
	'open'	=>'\[',
	'close'=>'\]',
	'vl'	=> '\b[a-z0-9][a-z0-9_-]*:[a-z0-9][a-z0-9_-]*\b',  # FIELDS
	'method'=>'\.[a-z][a-z0-9_]*\b',
	'nm'	=> '\b[a-z][a-z0-9_-]*\b',  # FIELDS
	'sep'	=>':',
	'z'		=> ',\s*',
	'eq'	=> '=',
	'num'	=> '\b[0-9]+(?:\.[0-9]+)?\b',  # NUM
	'str1'	=> '\'(?:\\\\.|[^\'])*\'', # STRINGS 
	'str2'	=> '"(?:\\\\.|[^"])*"'
);

$this->maps = array(
	'text' => array(
		'text'	=> array('start' => '+text,expr', 'var'=>'text', 'raw'=>'text', 'end'=>'end')
	),
	'expr'=> array(
		'expr'	=> array('vl'=>'vl', 'nm'=>'call'),
		'vl'	=> array('fin'=>'-'),
		
		'call'	=> array('method'=>'mthd', 'open'=>'prms', 'fin'=>'-'),
		'mthd'	=> array('open'=>'prms', 'fin'=>'-'),
	
		'prms'	=> array('nm'=>'prnm'),
		'prnm'	=> array('eq'=>'eq'),
		'eq'	=> array('vl'=>'prvl', 'var'=>'prvl', 'str1'=>'prvl', 'str2'=>'prvl', 'num'=>'prvl', 'nm'=>'prvl'),
		'prvl'	=> array('z'=>'prms', 'close'=>'close'),
		'close' => array('fin'=>'-')
	)
);
?>