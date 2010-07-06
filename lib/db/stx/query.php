<?php
$dnm = "[a-z][.a-z0-9_-]*";

/* this verison of PostgesSQL */

$this->ignoreSpace = true;

$this->terms = array(
	'bool'	=> '\btrue|false|null\b',  # BOOLS
	'case'  => '\(\?',
    'deq'   => '==',
    'arrow' => '=>',
	'open2a' => '\+\[',
	'open2b' => '~\[',
	'open2c' => '@\[',
    'else'  => '\belse\b',
	'cond'	=> '\?',
    'op3'   => '!in\b',
	'op2'	=> '\b(?:like|in|or|and|xor|between|from|is)\b',
	'not'	=> '\bnot|any\b',
	'limit'	=> '\#[0-9]+', # #1,5
	'jnm'	=> "[*.]$dnm", # .closes
	'order'	=> "[+-]$dnm", # +order -time
	'flag'	=> "@(?:[fmlrdsi]{1,2})", # @id
	'group'	=> "@$dnm", # @id
	'typecast'=> "::[a-z][a-z0-9]*\b",
	'alias'	=> ":[a-z][a-z0-9_]*\b", # @id
	'func'	=> '\b[a-z][a-z0-9_]+\(', # FUNCTION
	'dnm'	=> "\b$dnm\b", #
	'num'	=> '[0-9]+(?:\.[0-9]+)?',  # NUM
	'mult'	=> '\*',
	'op'	=> '(?:!=|[*\/]|[<>]=?)|=|[|]{2}|&&', # SYMBOLS
	'unop'	=> '[+-]',
	'z'		=> ',',
	'open'	=> '\(',
	'close' => '\)',
	'open2' => '\[',
	'close2'=> '\]',
	'dist' => '!',
	'space' => '\s+',
	'str1'	=> '\'(?:\\\\.|[^\'])*\'', # STRINGS 
	'str2'	=> '"(?:\\\\.|[^"])*"' # STRINGS 			
);

$this->maps = array();

$this->maps['main'] = array(
	'begin'		=> array('dist'=>'dist', 'dnm'=> 'maintable'),
	'dist'		=> array('dnm'=> 'maintable'),
	'maintable' => array(				 'order'=>'order', 'limit'=>'limit', 'end'=>'end', 'group'=>'group', 'open2'=>'+where,where', 'open2c'=>'+having,where', 'open' => '+fset,fset', 'alias'=>'mtalias' ),
	'mtalias'	=> array('jnm'=>'table', 'order'=>'order', 'limit'=>'limit', 'end'=>'end', 'group'=>'group', 'open2'=>'+where,where', 'open2c'=>'+having,where', 'open' => '+fset,fset'),
	'table'		=> array('jnm'=>'table', 'order'=>'order', 'limit'=>'limit', 'end'=>'end', 'group'=>'group', 'open2'=>'+where,where', 'open2c'=>'+having,where', 'open' => '+fset,fset', 'open2a,open2b'=>'+joincl,where', 'alias'=>'alias'),
	'alias'		=> array('jnm'=>'table', 'order'=>'order', 'limit'=>'limit', 'end'=>'end', 'group'=>'group', 'open2'=>'+where,where', 'open2c'=>'+having,where', 'open' => '+fset,fset', 'open2a,open2b'=>'+joincl,where'),
	'joincl'	=> array('jnm'=>'table', 'order'=>'order', 'limit'=>'limit', 'end'=>'end', 'group'=>'group', 'open2'=>'+where,where', 'open2c'=>'+having,where', 'open' => '+fset,fset'),
	'fset'   	=> array('jnm'=>'table', 'order'=>'order', 'limit'=>'limit', 'end'=>'end', 'group'=>'group', 'open2'=>'+where,where', 'open2c'=>'+having,where'),
	'where'  	=> array('jnm'=>'table', 'order'=>'order', 'limit'=>'limit', 'end'=>'end', 'group'=>'group', 'open2'=>'+where,where', 'open2c'=>'+having,where'),
	'group'  	=> array('jnm'=>'table', 'order'=>'order', 'limit'=>'limit', 'end'=>'end', 'group'=>'group', 'open2c'=>'+having,where'),
	'having'	=> array('jnm'=>'table', 'order'=>'order', 'limit'=>'limit', 'end'=>'end', 'open2c'=>'+having,where'),
	'order'		=> array('jnm'=>'table', 'order'=>'order', 'limit'=>'limit', 'end'=>'end'),
	'limit'		=> array('z'=>'z', 'end'=>'end'),
	'z' 		=> array('num'=>'offset'),
	'offset' 	=> array('end'=>'end')
);

$unop = array('dnm,num,bool,str1,str2,alias'=>'arg', 'func'=>'+arg,func', 'open'=>'+arg, expr', 'case'=>'+arg,case');
$op = $unop; $op['unop,not'] = 'unop';

$this->maps['fset'] = array(
	'begin'		=> $op, 
	'operator'	=> $op,
	'next'		=> $op,
	'unop' 		=> $unop,
	'arg'		=> array('z' => 'next', 'close'=>'-', 'op,unop,mult,op2,op3'=>'operator', 'alias'=>'alias', 'typecast'=>'typecast',	'flag'=>'flag'),
	'typecast'  => array('z' => 'next', 'close'=>'-', 'flag'=>'flag', 'alias'=>'alias'),
	'alias'		=> array('z' => 'next', 'close'=>'-', 'flag'=>'flag'),
	'flag'		=> array('z' => 'next', 'close'=>'-'),
	'any'		=> array('close'=>'-')
);
$this->maps['fset']['begin']['close'] = '-';
$this->maps['fset']['begin']['mult'] = 'any';

$this->maps['expr'] = array(
	'begin'		=> $op, 
	'operator'	=> $op,
	'next'		=> $op,
	'unop' 		=> $unop,
    'typecast'  => array('z' => 'next', 'close'=>'-', 'op,unop,op2,op3'=>'operator'),
	'arg'		=> array('z' => 'next', 'close'=>'-', 'op,unop,op2,op3'=>'operator', 'typecast'=>'typecast')
);

$this->maps['func'] = $this->maps['expr'];
$this->maps['func']['begin']['close'] = '-';

$this->maps['where'] = array(
	'begin'		=> $op, 
	'operator'	=> $op,
	'cond'		=> $op, 
	'unop' 		=> $unop,
    'typecast'  => array('close2'=>'-', 'op,unop,op2,op3'=>'operator', 'alias'=>'alias'),
	'arg'		=> array('close2'=>'-', 'op,unop,op2,op3'=>'operator', 'alias'=>'alias', 'typecast'=>'typecast')
);
$this->maps['where']['begin']['cond'] = 'cond';

$this->maps['case'] = array(
	'begin'		=> $op, 
	'operator'	=> $op,
	'deq'	=> $op,
	'arrow'	=> $op,
	'else'	=> $op,
	'next'		=> $op,
	'unop' 		=> $unop,
    'typecast'  => array('z' => 'next', 'close'=>'-', 'op,unop,op2,op3'=>'operator', 'deq'=>'deq', 'arrow'=>'arrow', 'else'=>'else'),
	'arg'		=> array('z' => 'next', 'close'=>'-', 'op,unop,op2,op3'=>'operator', 'deq'=>'deq', 'arrow'=>'arrow', 'else'=>'else', 'typecast'=>'typecast')
)

?>