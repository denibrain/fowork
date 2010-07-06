<?php
namespace FW\DB;
	
/*			
e  = equal filter
m = middle
l = left
r = right
s = sort
d = default sort
*/

define("FW_DSM_STATIC", 0);
define("FW_DSM_DYNAMIC", 1);

class QueryField {
	public $name = false;
	public $text = '';
	public $computed = false;
	public $aliased = false;
	public $typecast = false;
}

class DataSet extends \FW\Object implements \IteratorAggregate  {
	
	private $mode = FW_DSM_STATIC;
	
	// static SQL expression part
	private $sql;

	// DB connection
	private $db;
	
	// External values
	private $params;
	
	// Query properties
	private $mainTable;
	private $fieldAlias = array();
	private $tableAlias = array();
	private $sortables = array();
	private $filterMask = '';
	
	// Internal process properties for parse queries
	private $curTable = false;
	private $curField;
	private $expr;
	private $exprStack = array();
	private $mask = array();
	private $cond = false;
	private $undefinedTables = array();
	
	// Query components
	private $distinct = false;
	private $where = array();
	private $order = array();
	private $group = array();
	private $having = array();
	private $tables = array();
	private $fields = array();
	private $limit = false;
	private $offset = false;
	
	// Data Access properties
	private $row = 0;
	private $query = '';
	private $opened = false;
	private $data;
	private $q;
	private $count = false;
	
	// Dynamic
	private $filter = false;
	private $sort = false;
	private $sortValue;
	private $filterValue;
	private $defaultSort = false;
	private $invertDefaultSort = false;

	// Parsers 	
	static private $fieldParse = false;
	static private $queryParse = false;
	
	static public function factory($query, $params) {
		return new DataSet($query, $params);
	}
	
	static public function init() {
		self::$fieldParse = new \FW\Text\Parser(FW_LIB.'db/stx/field.php');
		self::$queryParse = new \FW\Text\Parser(FW_LIB.'db/stx/query.php');
	}
	
	public function __construct($query, $params = array(), $db = null) {
		$this->db = !$db ? \FW\App\App::$instance->db : $db;
		
		$this->params = $params;

		$query = preg_replace_callback("/__([a-z0-9]+)__/i",
			function ($matches) use ($params) {
				return isset($params[$matches[1]]) ? $params[$matches[1]] : '';
			}, $query);

		if (preg_match("/^[\s(]*SELECT/", $query)) {
			$this->sql = $query;
			$this->mode = FW_DSM_STATIC;
		}
		else {
			self::$queryParse->compile($query, array($this, 'proceedQuery'));
			$this->mode = FW_DSM_DYNAMIC;
		}
		
		$this->row = 0;
		$this->opened = false;
	}

	public function open() {
		$sql = $this->__get('sql');
		try {
			$this->q = $this->db->execute($sql);
		}
		catch (\Exception $e) {
			throw new \Exception("Invalid sql $sql ".$e->getMessage());
		}
		$this->opened = true;
		return $this->q;
	}
	
	function getIterator() {
		if (!$this->opened) $this->open();
		return $this->q;
	}
	
	/* User help tools */
	public function dic() {
		$this->open();
		return $this->q->dic();
	}

	public function lst() {
		$this->open();
		return $this->q->lst();
	}
	
	public function items($mixed = '', $itemName = 'item', $mapper = NULL) {
		$this->open();
		return $this->q->items($mixed, $itemName, $mapper);
	}
	
	/* Item method */
	public function seek($row = 0) { $this->q->seek($row); }
	public function count() { if (!$this->opened) $this->open(); return $this->q->count(); }
	public function get() { if (!$this->opened) $this->open(); return $this->q->get();}
	public function val() { if (!$this->opened) $this->open(); return $this->q->val();}
	public function getA() { if (!$this->opened) $this->open(); return $this->q->getA();}
	
	/* Getter/Setter */
	public function __get($key) {
		switch($key) {
			case 'sql': return $this->mode == FW_DSM_STATIC ? $this->sql : $this->build();
			case 'sort': return $this->sort;
			case 'params': return $this->params;
			case 'filter': return $this->filter;
			case 'count': return $this->count === false ? $this->count = $this->count() : $this->count;
			default: return parent::__get($key);
		}
	}
	
	public function __set($key, $value) {
		switch($key) {
			case 'filter' : $this->setFilter($value); break;
			case 'sort': $this->setSort($value);break;
			case 'where': $this->where($value);break;
			case 'limit': $this->limit = (int)$value;break;
			case 'offset': $this->offset = (int)$value;break;
			default: parent::__set($key, $value);
		}
	}
	
	private function build() {
		$sql = $this->sql;

		$where = $this->where;
		if ($this->filter) $where[] = $this->filterValue;
		if ($where) $sql .= " WHERE (".implode(') AND (', $where).")";

		if ($this->group) $sql .= " GROUP BY ".implode(', ', $this->group);
		if ($this->having) $sql .=" HAVING (".implode(') AND (', $this->having).")";

		$order = $this->order;
		if ($this->sort) $order[] = $this->sortValue;
		if ($order) $sql .= " ORDER BY ".implode(', ', $order);

		if (false!==$this->limit) $sql.= " LIMIT $this->limit";
		if (false!==$this->offset) $sql.= " OFFSET $this->offset";
		
		//\FW\App\App::$instance->log("[$sql<br/>]");
		return $sql;
	}
	
	private function setSort($field) {
		$dir = substr($field, 0, 1);
		$fieldx = substr($field, 1);
		
		if (!isset($this->sortables[$fieldx]))
			throw new \Exception("Cannot sort by $fieldx");
	
		$this->sortValue =  $this->sortables[$fieldx].($dir == '-'?' DESC': ' ASC');
		$this->sort = $field;
	}
	
	private function setFilter($value) {
		$value = str_replace("'", "\\'", preg_replace("/[^%#a-z0-9Р-пр-џИЈ\"'-]+/i", '', $value));
		if ($this->filterMask && $value) {
			$this->filter = $value;
			$this->filterValue = str_replace('@', $value, $this->filterMask);
		}
		else $this->filter = '';		
	}
	
	public function where($expression) {
		$this->expr = "";
		self::$fieldParse->compile($expression, array($this, 'proceedWhere'));
		$this->where[] = $this->expr;
		return $this;
	}
	
	/* Parsers */
	public function proceedQuery($type, $v, $pos, $proc) {
		if ($proc->mapName == 'main') $f = 'prMain';
		else $f = 'prWhere';
		$this->$f($type, $v, $pos, $proc);
	}

	public function proceedWhere($type, $v, $pos, $proc) {
		switch($type) {
			case 'alias':
				$v = $this->db->proceedValue($this->params[substr($v,1)]);
				break;
			case 'dnm':
				$v = $this->parseField($v);
				break;
			case 'z': $v = ', '; break;
			case 'op2':
			case 'op':
			case 'unop':
				$v = " ".strtoupper($v);
				if ($proc->state =='operator') $v.=" ";
				break;
		}
		$this->expr .= $v;
	}
	
	private function prMain($type, $v, $pos, $proc) {
		switch ($type) {
			case 'dist':	$this->distinct = true; break;
			case 'dnm': 	$this->curTable = $this->mainTable =  $this->addTable($v); break;
			case 'order':	$this->order[] = $this->parseField(substr($v, 1), true).(substr($v, 0, 1) == '+'? ' ASC':' DESC'); break;
			case 'group':	$this->group[] = $this->parseField(substr($v, 1)); break;
			case 'limit':	$this->limit = (int)substr($v, 1); 	break;
			case 'offset':	$this->offset = (int)substr($v, 1);break;
			case 'mtalias':
			case 'alias':
				$v = substr($v, 1);
				if (isset($this->tableAlias[$v])) throw new \Exception("Duplicate table alias $v");
				$this->tables[$this->tableAlias[$this->curTable]]['alias'] = $v;
				$this->tableAlias[$v]=$this->tableAlias[$this->curTable];
				$this->curTable = $v;
				if (isset($this->undefinedTables[$v])) unset($this->undefinedTables[$v]);
				break;
			
			case 'jnm':
				$this->curTable = $this->addTable(substr($v, 1), substr($v, 0, 1)=='.');
				break;
				
			case 'where.end':
				switch($proc->state) {
					case 'where': if (!$this->cond) $this->where[] = $this->expr; break;
					case 'joincl': $this->tables[$this->tableAlias[$this->curTable]]['joincl'][] = $this->expr; break;
					case 'having': if (!$this->cond) $this->having[] = $this->expr; break;
				}
				break;

			case 'case':
				$this->curField = new QueryField();
				$this->expr = 'CASE ';
				break;
				
			case 'open': # field set
				$this->curField = new QueryField();
				$this->expr = '';
				break;
			case 'open2b': # alter join clasure
				$this->tables[$this->tableAlias[$this->curTable]]['ex'] = 1;
			case 'open2':  # where clasure
			case 'open2a': # join clasure
			case 'open2c':  # having clasure
				$this->expr = '';
				$this->cond = false;
				break;
			
			case 'end':
				$this->compute();
				$this->curTable = $this->mainTable;
		}
	}
	
	private function prWhere($type, $v, $pos, $proc) {
		switch ($type) {
			
			case 'z':
			case 'close':
				if ($proc->mapName == 'fset') {
					if (!$this->curField->computed) $this->computeCurrentField();
					if ($type == 'z') $this->curField = new QueryField();
				} elseif ($proc->mapName == 'func' || $proc->mapName == 'expr' ) {
					$this->expr .= $type == 'z' ? ', ':')';
				} elseif ($proc->mapName == 'case') {
					$this->expr .= $type == 'z' ? ' WHERE ':' END)';
				}
				break;
			case 'typecast':
				$this->expr	.= $v;
				break;
			
			case 'deq':
				$this->expr .= ' WHEN ';
				break;
			case 'arrow':
				$this->expr .= ' THEN ';
				break;
			case 'else':
				$this->expr .= ' ELSE ';
				break;
			case 'flag':
				list($name, $e)  = $this->computeCurrentField();
				foreach(str_split(substr($v, 1), 1) as $flag) {
					if ($flag =='d' || $flag == 's' || $flag == 'i') {
						if ($flag =='d' || $flag == 'i') {
							if (false!==$this->defaultSort)
								throw new Exception('Canot be two default sort');
							$this->defaultSort = $name;
							if ($flag == 'i') $this->invertDefaultSort = true;
						} 
						$this->sortables[$name] = $e;
					}
					else { # filter
						$tm = array('f'=> " = '@'", 'm'=> " ILIKE '%@%'", 'r'=> " ILIKE '%@'", 'l'=> " ILIKE '@%'");
						$this->mask[] = $e."::varchar".$tm[$flag];
					}
				}
				break;

			case 'dnm':
				if ($proc->mapName == 'fset' && !$this->expr) {
					if (false!==($pos = strrpos($v, '.')))
						$this->curField->name = substr($v, $pos + 1);
					else
						$this->curField->name = $v;
				}
				$this->expr .= $this->parseField($v);
				break;
			
			case 'cond':
				$this->cond = 0;
				break;
			
			case 'alias':
				$v = substr($v, 1);
				if ($proc->mapName == 'fset') {
					$this->curField->name = $v;
					$this->curField->aliased = true;
				} else {
					if (isset($this->params[$v])) 
						$this->expr .= $this->db->proceedValue($this->params[$v]);
					elseif($this->cond === false) throw new \Exception("Param $v is absent");
					else ++$this->cond;
				}
				break;
			
			case 'typecast':
				$this->curField->typecast = substr($v, 2);
			
			case 'bool':
				$v = strtoupper($v);
			case 'str1':
			case 'str2':
				$self = $this;
				$v = preg_replace_callback('/:{([a-z0-9]+)}/i',
					function($matches) use ($self){
						$v = $matches[1];
						if (isset($self->params[$v])) return $self->params[$v];
						if($self->cond === false) throw new \Exception("Param $v is absent");
						else ++$self->cond;						
					}, $v);
			case 'num':
				$this->expr .= $v;
				break;
			
			case 'mult':
				if ($proc->state == 'begin') {
					$this->expr = $this->db->q($this->curTable).".".($this->curField->name = '*');
					break;
				}
				
			case 'op3':
				$v = 'NOT '.substr($v, 1);
			case 'op':
			case 'unop':
			case 'op2':
			case 'not':
				$v = " ".strtoupper($v);
				if ($proc->state == 'arg') $v.=" ";
				$this->expr .= $v;

				if ($proc->mapName == 'fset') $this->curField->name = '';
				break;
			
			case 'case':
				$v = '(CASE ';
			case 'open':
			case 'func':
				array_push($this->exprStack, $this->expr.$v);
				$this->expr = '';
				
				if ($proc->mapName == 'fset') $this->curField->name = '';
				break;
			
			case 'case.end':
			case 'func.end':
			case 'expr.end':
				$this->expr = array_pop($this->exprStack).$this->expr;
				break;
		}
	}
	
	private function parseField($f, $useAliases = false) {
		if (false===($pos = strrpos($f,'.'))) {
			if (!$useAliases || !isset($this->fieldAlias[$f])) {
				return "$this->curTable.".$this->db->q($f);
			}
		}
		else {
			$n = substr($f, 0, $pos);
			if (!isset($this->tableAlias[$n])) {
				if (isset($this->tables[$n])) $n = $this->tables[$n]['alias'];
				else {
					$this->undefinedTables[$n] = $n;
				}
			}
			$name = substr($f, $pos + 1);
			$f = "$n.$name";
		}
		return $this->db->q($f);
	}
	
	private function computeCurrentField() {
		if (''==$this->curField->name) {
			$name = $this->curField->name = 'f_'.count($this->fields);
			$this->curField->aliased = true;
		} else $name = $this->curField->name;

		if (isset($this->fields[$name]))
			throw new EDB("Duplicate field name [{$name}]");
		
		if ($this->curField->aliased)
			$this->fieldAlias[$name] = $this->expr;

		$this->fields[$name] = $this->expr.
			($this->curField->typecast?" ::{$this->curField->typecast}":'').
			($this->curField->aliased?" AS $name":'');
		$this->curField->computed = $this->expr;
		$this->expr = '';
		return array($name, $this->curField->computed);
	}
	
	function addTable($name, $outer =  false) {
		if (isset($this->tables[$name])) return $this->tables[$name]['alias'];
		if (isset($this->undefinedTables[$name])) unset($this->undefinedTables[$name]);
		
		$this->tables[$name] = array(
			'alias' => $a = 't'.count($this->tables),
			'outer' => $outer,
			'joincl' => array(),
			'ex' => 0
		);
		$this->tableAlias[$a] = $name;
		return $a;
	}

	private function compute() {
		$sql = 'SELECT ';
		if ($this->distinct) $sql .= "DISTINCT ";
		
		if (!$this->fields) $sql .= '*';
		else {
			$this->filterMask = implode(" OR ", $this->mask);
			$sql .= implode(", ", $this->fields);
		}

		$tkeys = array_keys($this->tables);
		$main = array_shift($tkeys);
		$ma  = $this->tables[$main]['alias'];
		$sql.= " FROM ".$this->db->q($main)." AS $ma";

		$prev = false;
		
		$no = 0;
		foreach($tkeys as $no => $key) {
			$tinfo = $this->tables[$key];
			$sql.= !$tinfo['outer'] ?" INNER":" LEFT";
			$sql.= " JOIN ".$this->db->q($key). "AS {$tinfo['alias']} ON ";
			
			$relation = false;
			if (!$tinfo['ex']) {
				$relation = $this->db->relation($main, $key, $ma, $tinfo['alias']);
				if (!$relation && $no) {
					$prev = $tkeys[$no - 1];
					$relation =	$this->db->relation($prev, $key,
						$this->tables[$prev]['alias'], $tinfo['alias']);
					
					if (!$relation && $no > 1) {
						foreach($tkeys as $i=>$tk) if ($i!=$no && $i != $no - 1) {
							$t = $this->tables[$tk];
							if (false!==($relation =
								$this->db->relation($tk, $key, $t['alias'], $tinfo['alias'])))
								break;
						}
					}
				}
				if (false===$relation) {
					print_r($tkeys);
					throw new \Exception("Can not find relation for table $key");
				}
				$sql .= $relation;
			}
			if ($tinfo['joincl']) {
				if (!$tinfo['ex']) $sql.= ' AND ';
				$sql.= "(".implode(") AND (", $tinfo['joincl']).")";
			}
		}
		$this->sql = $sql;
		
		if (count($this->sortables)) {
			if (!$this->defaultSort) $this->defaultSort = key($this->sortables);
			
			$this->setSort(($this->invertDefaultSort ? '-':'+').$this->defaultSort);
		}
		
		if (count($this->undefinedTables)) {
			throw new \Exception("Undefined tables: ". implode(', ', $this->undefinedTables));
		}
	}
	
	function __toString() { return $this->__get('sql'); }
}

// initializate
DataSet::init();

?>