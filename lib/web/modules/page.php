<?php
class Page extends FW\App\Module {
	
	protected $params = array();

	private $css;
	private $js;

	private $title;
	private $keywords = ''; // keywords
	private $description = ''; // description of the page;
	
	private $caption = 'No caption'; // caption of page
	private $parentIds = ''; // used for displayMap; build in buildPath;
	
	private $path;  // real path   : A/A.2/A.2.B/A.2.B.4
 	private $path2; // path of mask: A/A.*/A.*.B/A.*.B.*
	
	private $charset = FW_CHARSET;

	private $id;  // A.*.*
	private $url;
	
	private $tablename = FW_TBL_PAGE;
	
	function __construct($app) {
		parent::__construct($app);
	}

	public function compile($url) {
		/* устанока начальных значений */
		$this->url = $url;
		$this->id = $url->mask;
		$this->path = array();
		$this->path2 = array();
		$this->params = array_reverse($this->url->domain);
		
		/* поиск страницы */
		$ds = $this->dsActive(array('id'=>$this->id));
		if (!(list($caption, $expression, $aclevel, $template) =  $ds->get()))
			throw new E404();
			
		$this->caption = $this->app->resolve('*' == $caption ? "{".$expression."}" : $caption,
			$this->params);

		$this->app->checkLevel($aclevel);
		
		/* построение пути */
		$this->params['$'] = $this->url->address;
		$this->buildPath();

		/* построение тела страницы */
		$contents = array();
		
		$h = new \FW\App\THCall($this->params, 'display');
		if ($expression) {
			$expression = explode(';', $expression);
			foreach($expression as $c) {
				if (preg_match('/^([a-z0-9]+)=(.*)/', $c, $regs)) {
					list(, $name, $c) = $regs;
				} else $name = 'text';
				$contents[$name] = $this->app->content($c, $h);
			}
		}
	
		$templateFile = FW_PTH_DESIGN."templates/$template.html";
		if (!file_exists($templateFile) or !is_readable($templateFile))
			throw new \Exception("Template $template not found or cannot read!");
			
		$templateText = file_get_contents($templateFile);
		
		$bodyTemplate = FW_PTH_CONTENT.FW_TBL_PAGE.".art/".
				($this->id ? str_replace('*', '_', $this->id) : '!');

		if (file_exists($bodyTemplate)) {
			if (!is_readable($bodyTemplate))
				throw new \Exception("BodyTemplate $bodyTemplate cannot read!");
			$templateText = str_replace('{text}', file_get_contents($bodyTemplate), $templateText);
		}
		
		$app = $this->app;
		$template = new \FW\Text\ParametricTemplate();
		$template->setText(
			$templateText,
			function($field, $value) use ($app, $h) {
				return $app->content($value, $h);
			});

		if ($this->keywords) $contents['keywords'] = $this->keywords;
		if ($this->description) $contents['description'] = $this->description;
		//$content['js'] ....
		//$content['css']....
		
		$content = new \FW\Web\Content('html');
		$content->body = $template->compile($contents);
		return $content;
	}

	function __call($name, $args) {
		if (substr($name, 0, 2) == 'ds') $args[0]['table'] = $this->tablename;
		return parent::__call($name, $args);
	}
	
	function __get($key) {
		switch($key) {
			case 'tablename': return $this->tablename;
			case 'caption': return $this->caption;
			case 'url': return $this->url;
			case 'title': return implode(" - ", $this->path);
			case 'id': return $this->id;
			default: return parent::__get($key);
		}
	}

	function __set($key, $value) {
		switch($key) {
			case 'keywords': $this->keywords = $value; break;
			case 'description': $this->description = $value; break;
			case 'tablename': $this->tablename = $value; break;
			default: parent::__set($key, $value);
		}
	}

	private function buildPath() {
		$parts = $this->url->domain;

		// drop last (final url)
		array_pop($parts);
		if ($parts) {
			
			$parents = array();
			$mask = $this->url->maskdomain;
	
			// set first domains
			list(,$url) = each($mask);  // $url is a mask like this A.*.* 
			$valueurl = array_shift($parts); // $valueurl is real url like this A.4.56
			
			$params = array($valueurl, '$'=>$this->params['$']);
			$urls[$url] = array($valueurl, $params);
			
			// set second, third....
			foreach($parts as $key => $part) {
				array_unshift($params, $part); 
				$valueurl.= ".$part";
				$url .= ".".$mask[$key + 1];
				$urls[$url] = array($valueurl, $params);
			}
			
			$this->path2 = $urls;
			$this->parentIds = array_keys($urls);
			
			$ds = $this->dsParents(A('parents', $this->parentIds));
			if ($ds->count() != count($urls))
				throw new \Exception('System Integrity Error (pages)');
				
			while (list($id, $expression, $aclevel, $page) = $ds->get()) {
				$this->app->checkLevel($aclevel);
				list($valueurl, $params) = $urls[$id];
				$this->path[$valueurl] = $this->app->resolve($expression == '*' ? "{"."$page}" : $expression, $params);
			}
		}
			
		$this->path[(string)$this->url] = $this->caption;
		$this->path2[$this->id] = array((string)$this->url, $this->params);
	}
	
	function outtree(&$path, &$tree, $e) {
		if (count($path))
		foreach ($path as $key => $value) {
			$a = $e->add(E(array('id'=>$key)));
			list($a->caption, $a->href, $selected) = $value;
			if ($selected) $a->selected = 1;
			if(isset($tree[$key]) && count($tree[$key])) $this->outtree($tree[$key], $tree, $a);
		}
	}

	function getMap($startUrl = '', $onlyActive = false, $onmenu = false) {
		$params['startUrl'] = $startUrl;
		if ($onlyActive) {
			$params['parents'] = $this->parentIds;
			if ($startUrl) $params['parents'][] = '';
			$params['parents'][] = $this->id;
		}
		$ds->where = "parent_id = ANY (:parents)";
		if ($onmenu) $params['onmenu'] = true;

		//$params['aclevels'] = \App::$instance->user->groups;
		
		$ds = $this->dsMap($params);
		
		$tree = array(); // it's final tree
		$nodes = array($startUrl => array($startUrl => array_reverse(explode('.', $startUrl))));
		while(list($id, $parent, $name, $caption, $mapex, $href, $page) = $ds->get()) {
			if ('*' !== $name) {
				/* static page */
				foreach($nodes[$parent] as $nodeurl => $params) {
					$realid = "$nodeurl.$name";
					$h= !$href ? $realid : $href;
					array_unshift($params, $name);
					$selected = isset($this->path[$realid]);
					if ($selected)
						$caption = $this->path[$realid];
					else {
						$caption = $this->app->resolve($caption == '*' ? "{"."$page}" : $caption, $params);
					}
					$tree[$nodeurl][$realid] = array($caption, $h, $selected);
					if (!$onlyActive || $selected) $nodes[$id][$realid] = $params;
				}
			} else {
				if ($mapex) {
					// expand mode
					foreach($nodes[$parent] as $nodeurl => $params) {
						$map = $this->app->call($mapex, $params, 'map');
						foreach($map as $key=>$caption) {
							$realid = "$nodeurl.$key";
							$tree[$nodeurl][$realid] = array(
								$caption, $realid, $selected = isset($this->path[$realid])
							);
							if (!$onlyActive || $selected)
								$nodes[$id][$realid] = array_merge(array($key), $params);
						}
					}
				} else {
					// only selected mode
					if (($ex = array_intersect_key($nodes[$parent], $this->path)) && isset($this->path2[$id])) {
						list($realid, $params) = $this->path2[$id];
						$tree[key($ex)][$realid] = array(
							$this->path[$realid],$realid, $selected = true
						);
						$nodes[$id][$realid] = $params;
					}
				}
			}
		}
		$root = E();
		$root->selected = $s = (int)('' === $startUrl || isset($this->path[$startUrl]));
		$root->caption = ($s && '' !== $startUrl) ? $this->path[$startUrl] : '';
		$root->href = $startUrl;
		$root->id = $startUrl;

		$list = array($startUrl=>$root);
		foreach($tree as $parentKey => $childs) {
			if (isset($list[$parentKey])) $p = $list[$parentKey];
			else $p = $list[$parentKey] = E();
			foreach($childs as $key => $value) {
				if (isset($list[$key])) $a = $p->add($list[$key]);
				else $a = $p->add($list[$key] = E());
				$a->id = $key;
				list($a->caption, $a->href, $selected) = $value;
				if ($selected) $a->selected = 1;
			}
		}
		
//		print_r($list);
//		$this->outtree($tree[$startUrl], $tree, $root);
		return $root;
	}
	
	/* content methods */
	
	function displayMenu($params) {
		$dsp = $params = A('selected', $this->url->domain[0]);
		$dsp['usergroup'] = $this->app->user->groups;
		return $this->dsLevel1($dsp)->items(E('menu', $params));
	}

	function displayPath($params) {
		$path = E('path');
		foreach($this->path as $key=>$value)
			$path->add(E('item', array("href"=>$key, 'name'=>$value)));
		return $path;
	}
	
	function displaySubmenu($params) {
		return $this->getMap($this->url->domain[0], $params['active'], true);
	}
	
	function displayMap() {
		return $this->getMap();
	}
}
?>