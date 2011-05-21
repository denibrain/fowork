<?php
$this->ignoreSpace = true;

$this->terms = array(
	'var'	=> '\$[$0-9a-z]+',
	'open'	=> '\\[',
	'close'	=> '\\]',
	'vl'	=> '\b[a-z][a-z0-9_-]*:[a-z][a-z0-9_-]*\b',  # FIELDS
	'tpl'	=> '@(?:[a-z][a-z0-9_-]*\b)?',  # FIELDS
	'method'=> '\.[a-z][a-z0-9_]*\b',
	'nm'	=> '\b[a-z][a-z0-9_-]*\b',  # FIELDS
	'z'		=> ',',
	'a'		=> '!',
	'eq'	=> '=',
	'space' => '\s+',
	'num'	=> '\b[0-9]+(?:\.[0-9]+)?\b',  # NUM
	'str1'	=> '\'(?:\\\\.|[^\'])*\'', # STRINGS 
	'str2'	=> '"(?:\\\\.|[^"])*"'
);

$this->maps = array(
	'main'=>array(
		'expr'	=> array('vl'=>'vl', 'nm'=>'call'),

		'vl' 	=> array('a'=>'notpl', 'tpl'=>'templ', 'end'=>'end'),
		'close' => array('a'=>'notpl', 'tpl'=>'templ', 'end'=>'end'),
		
		'call'	=> array('method'=>'mthd', 'open'=>'prms', 'tpl'=> 'templ', 'a'=>'notpl', 'end'=>'end'),
		'mthd'	=> array(                  'open'=>'prms', 'tpl'=> 'templ', 'a'=>'notpl', 'end'=>'end'),
	
		'prms'	=> array('nm'=>'prnm'),
		'prnm'	=> array('eq'=>'eq'),
		'eq'	=> array('nm'=>'prvl', 'vl'=>'prvl', 'var'=>'prvl', 'str1'=>'prvl', 'str2'=>'prvl', 'num'=>'prvl'),
		'prvl'	=> array('z'=>'prms', 'close'=>'close'),

		'templ'	=> array('end'=>'end'),
		'notpl' => array('end'=>'end')
	)

);

?>