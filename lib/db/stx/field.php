<?php
$dnm = "[a-z0-9][.a-z0-9_-]+";

$this->ignoreSpace = true;


$this->terms = array(
	'bool'	=> 'true|false',  # BOOLS
	'not'	=> 'not',
	'op2'	=> '\b(?:like|in|or|and|xor|between)\b',
	'alias'	=> ":$dnm", # @id
	'func'	=> '\b[a-z0-9][a-z0-9_]+\(', # FUNCTION
	'dnm'	=> "\b$dnm\b", #
	'num'	=> '\b[0-9]+(?:\.[0-9]+)?\b',  # NUM
	'mult'	=> '\*',
	'op'	=> '(?:!=|[*\/]|[<>]=?)|=|[|]{2}|&&', # SYMBOLS
	'unop'	=> '[+-]',
	'z'		=> ',',
	'open'	=> '\(',
	'close' => '\)',
	'space' => '\s+',
	'str1'	=> '\'(?:\\\\.|[^\'])*\'', # STRINGS 
	'str2'	=> '"(?:\\\\.|[^"])*"' # STRINGS 			
);


$this->maps = array();

$this->maps['where'] = array(
	'begin'		=> array('dnm,num,bool,str1,str2,alias'=>'arg', 'func'=>'+arg,func', 'open'=>'+arg, expr', 'unop,not'=>'unop'),
	'operator'	=> array('dnm,num,bool,str1,str2,alias'=>'arg', 'func'=>'+arg,func', 'open'=>'+arg, expr', 'unop,not'=>'unop'),
	'unop' 		=> array('dnm,num,bool,str1,str2,alias'=>'arg', 'func'=>'+arg,func', 'open'=>'+arg, expr'),
	'arg'		=> array('end'=>'end', 'op,unop,op2'=>'operator', 'alias'=>'alias')
);

$this->maps['expr'] = array(
	'begin'		=> array('dnm,num,bool,str1,str2,alias'=>'arg', 'func'=>'+arg,func', 'open'=>'+arg, expr', 'unop,not'=>'unop'),
	'operator'	=> array('dnm,num,bool,str1,str2,alias'=>'arg', 'func'=>'+arg,func', 'open'=>'+arg, expr', 'unop,not'=>'unop'),
	'next'		=> array('dnm,num,bool,str1,str2,alias'=>'arg', 'func'=>'+arg,func', 'open'=>'+arg, expr', 'unop,not'=>'unop'),
	'unop' 		=> array('dnm,num,bool,str1,str2,alias'=>'arg', 'func'=>'+arg,func', 'open'=>'+arg, expr'),
	'arg'		=> array('z' => 'next', 'close'=>'-', 'op,unop,op2'=>'operator')
);

$this->maps['func'] = $this->maps['expr'];
$this->maps['func']['begin']['close'] = '-';

?>