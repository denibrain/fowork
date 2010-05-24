<?php

class Search extends \FW\App\Module {

	function modulIndexing(&$modul) {
		$_pref = $modul->index_def['pref'];
		$_suff = $modul->index_def['suff'];
		$topic = $modul->listform_title;
		
		// erasing all
		$this->execSQL("DELETE {$this->tablename}, #word FROM {$this->tablename}, #word WHERE #word.doc_id = {$this->tablename}.id && {$this->tablename}.topic='" . $topic . "'");
		
		$memfld = array();
		$dbflds = array();
		foreach($modul->index_def['fields'] as $field) {
			if(isset($modul->memos[$field])) {
				$memfld[] = $field;
			} else
				$dbflds[] = $field;
		}
		
		$i = 0;
		echo "<br/><b>[{$modul->tablename}]</b> Starting....";
		$TBL = "FROM {$modul->tablename}";
		if ($modul->mf_actctrl) {
			$TBL .= ' WHERE active = 1';
			echo "Use only active";
		}
		$q = $this->execSQL('SELECT COUNT(id) '.$TBL);
		list($count) = $q->get();
		echo "($count)";
		$pref = 'SELECT ' . $modul->index_def['field_index'] . ' as id001, ' . implode(', ', $dbflds);
		//if ($region_n) $pref.=  ", $region_n";
		$pref .= " $TBL ORDER BY id LIMIT ";

		echo "<br/><img src='/admin/images/r.gif' height='11' vspace='3' width='400'><br>";
		$barid = 'b' . rand(0, 999);
		echo "<img src='/admin/images/r.gif' height='11' width='1' id='$barid'><br>";
		
		// portion
		$step = 300;
		$start = 0;
		
		$tm = $this->getmicrotime();
		
		$q = $this->execSQL($pref . $start . ',' . $step);
		$r = (int)$modul->index_def['rating'];
		$filepref = "{$this->kernel->PATH_WWW}content/".substr(get_class($modul), 0, -6);
		while($q->num_rows()) {
			while($row = $q->fetch_array()) {
				$id = array_shift($row);
				if(count($memfld)) foreach($memfld as $key) {
					$filename =  "$filepref.$key/$id.html";
					if(file_exists($filename)) $row[$key] = implode(" ", file($filename));
					else $row[$key] = "";
				}
				$url = $_pref . $id . $_suff;
				$name = $row[$modul->index_def['name']];
				$scrap = str_replace('&nbsp;', ' ', strip_tags(substr($row[$modul->index_def['scrap']], 0, 255)));
				$data = implode(" ", $row);
				$id = $this->add_document($url, $topic, $name, $scrap, $r, 1, $region_n ? $row[$region_n] : 0);
				
				$total++;
				$barwidth = $total * 400 / $count;
				eval('$bar="' . $scr . '";');
				echo $bar;
				flush();
				
				$this->add_text($data, $id);
			}
			flush();
			$start += $step;
			$q = $this->execSQL($pref . $start . ',' . $step);
		}
		echo "Finished";
		flush();
	}

	function indexing_record(&$modul) {
		$data = array();
		if(count($modul->index_def['fields'])) foreach($modul->index_def['fields'] as $field) {
			if(isset($modul->mmo_data[$field])) $b = $modul->mmo_data[$field];
			elseif(isset($modul->fld_data[$field])) $b = $modul->fld_data[$field];
			$data[$field] = isset($b) ? $b : '';
		}
		
		$url = $modul->index_def['pref'] . $modul->id . $modul->index_def['suff'];
		$scrap = substr($data[$modul->index_def['scrap']], 0, 255);
		
		$name = $data[$modul->index_def['name']];
		$topic = $modul->listform_title;
		$data = implode(" ", $data);
		
		$id = $this->add_document($url, $topic, $name, $scrap, isset($modul->index_def['rating']) ? (int)$modul->index_def['rating'] : 0, 0);
		$this->add_text($data, $id);
	
	}

	function add_document($url, $topic, $name, &$scrap, $rating, $add = 0, $region = 0) {
		
		if(!$add) {
			$q = $this->kernel->execSQL('SELECT id FROM ' . $this->tablename . ' WHERE url = "' . $url . '"');
			$n = $q->num_rows();
		} else
			$n = 0;
		
		$name = str_replace(array("\\", "'"), array("\\\\", "\\'"), $name);
		$scrap = str_replace(array("\\", "'"), array("\\\\", "\\'"), $scrap);
		$s = $this->tablename . " SET url='$url', topic='$topic', name='$name', scrap='$scrap', rating='$rating'";
		
		if(!$n) {
			$this->kernel->execSQL('INSERT ' . $s);
			$q = $this->kernel->execSQL('SELECT LAST_INSERT_ID()');
			list($id) = $q->fetch_row();
		} else {
			list($id) = $q->fetch_row();
			$this->kernel->execSQL('UPDATE ' . $s . ' WHERE id = ' . $id);
			$this->kernel->execSQL('DELETE FROM ' . $this->kernel->dbpref . 'word WHERE doc_id = ' . $id);
		}
		return $id;
	}

	function delete_document($url) {
		$this->kernel->sql_query = 'DELETE FROM t1, t2 USING ' . $this->tablename . ' as t1, ' . $this->kernel->dbpref . 'word as t2 WHERE t2.doc_id = t1.id && t1.url = "' . $url . '"';
		$this->kernel->sql_execute();
	}

	function my_unique($a) {
		$r = array();
		foreach($a as $w) {
			if(isset($r[$w])) ++$r[$w];
			else $r[$w] = 1;
		}
		return $r;
	}

	function add_text(&$text, $doc_id) {
		$q = $this->kernel->execSQL('SELECT MAX(id) FROM dics.dic');
		list($new_id) = $q->fetch_row();
		
		preg_match_all('/[0-9a-zA-Zа-€ј-я]+/', strtr(strip_tags($text), 'јЅ¬√ƒ≈®∆«»… ЋћЌќѕ–—“”‘’÷„ЎўџЁёя№Џ', 'абвгдеЄжзийклмнопрстуфхцчшщыэю€ьъ'), $regs);
		
		if(!count($regs[0])) return 0;
		// search id & grp
		$uwords = $this->my_unique($regs[0]);
		unset($regs[0]);
		$q = $this->kernel->execSQL('SELECT id, grp, wrd FROM dics.dic WHERE wrd IN ("' . implode('", "', array_keys($uwords)) . '")');
		$ct = array();
		while($data = $q->fetch_row())
			$ct[$data[2]] = array($data[0], $data[1]);
		$index = array();
		$add = array();
		foreach($uwords as $key => $cnt) {
			if(isset($ct[$key])) list($id, $grp) = $ct[$key];
			else {
				$grp = $id = ++$new_id;
				$add[] = '(' . $id . ', ' . $id . ', "' . $key . '")';
			}
			$index[] = '(' . $doc_id . ', ' . $cnt . ', ' . $id . ', ' . $grp . ')';
		}
		//1
		$n = count($uwords);
		unset($uwords);
		
		$this->kernel->execSQL('INSERT INTO #word (doc_id, weight, word_id, class_id) VALUES ' . implode(',', $index));
		if(!count($add)) return $n;
		
		$this->kernel->execSQL('INSERT INTO dics.dic (id, grp, wrd) VALUES ' . implode(',', $add));
		return $n;
	}

	function new_query($user_query, $topic) {
		## фильтр запроса, слва и цыфры
		preg_match_all('/[0-9a-zA-Zа-€ј-я]+/', strtr($user_query, 'јЅ¬√ƒ≈®∆«»… ЋћЌќѕ–—“”‘’÷„ЎўџЁёя№Џ', 'абвгдеЄжзийклмнопрстуфхцчшщыэю€ьъ'), $regs);
		
		## массив regs содержит слова запроса, а первый элемент - вс€ строка запроса
		## удаление первого элемента
		$regs = array_slice($regs[0], 0, 10);
		
		## сохранение параметров поиска в сессию
		$_SESSION['search_query']['id'] = md5(date('YmdHisM', time())); // хеш ключ запролса
		$_SESSION['search_query']['min'] = count($regs) << 1; // ?
		$_SESSION['search_query']['result'] = array(); // результаты поиска
		$_SESSION['search_query']['query'] = implode(' ', $regs); // стока запроса
		

		## кодирование строки запроса в числа из словар€
		// get words;
		$q = &$this->kernel->execSQL('SELECT id, grp FROM dics.dic WHERE wrd IN ("' . implode('", "', $regs) . '") GROUP BY id');
		$class_id = array();
		while(list($id, $group) = $q->fetch_row()) {
			$word_id[] = $id;
			$class_id[] = $group;
		}
		
		## проверка на пустой запрос
		if(!count($class_id)) return 0;
		
		## условие по виду контента (разделам)
		if($topic != "") $topic = " AND t2.topic = '" . $topic . "'";
		
		## запрос по индетичному вхождению слов из запроса
		$q = &$this->kernel->execSQL('SELECT sum(word.weight) as w, word.doc_id, t2.rating FROM #word as word, ' . $this->tablename . ' t2 WHERE t2.id = word.doc_id ' . $topic . ' AND word.word_id IN (' . implode(', ', $word_id) . ') GROUP BY word.doc_id ORDER BY w DESC, rating DESC LIMIT 1000');
		
		while(list($weight, $doc_id, $rating) = $q->fetch_row()) {
			$docs[$doc_id] = ($weight * $rating) << 1;
		}

		## запрос по морфлологичесмким группам
		$q = &$this->kernel->execSQL('SELECT sum(word.weight) as w, word.doc_id, t2.rating FROM #word as word, ' . $this->tablename . ' t2 WHERE t2.id = word.doc_id ' . $topic . ' AND word.class_id IN (' . implode(', ', $class_id) . ') GROUP BY word.doc_id, word.class_id ORDER BY w DESC, rating DESC LIMIT 1000');
		
		## массив $r содержит сколько слов из запроса найдено в документе (по группам)
		$r = array();
		while(list($weight, $doc_id, $rating) = $q->fetch_row()) {
			$docs[$doc_id] = isset($docs[$doc_id]) ? $docs[$doc_id] + ($weight * $rating) : ($weight * $rating);
			$r[$doc_id] = isset($r[$doc_id]) ? $r[$doc_id] + 1 : 1;
		}
		## порог 
		$n = 0;//count(array_unique($regs));
		
		## удал€ем все документы, у которых вхождение слов меньше порогого

		$newdocs = array();
		if(count($r)) foreach($r as $doc_id => $words) {
			if($words >= $n) $newdocs[$doc_id] = $docs[$doc_id];
		}
		$docs = $newdocs;
		
		if(!count($docs)) return 0;
		
		arsort($docs);
		$_SESSION['search_query']['result'] = $docs;
		return 0; 
	}

	function displayBox() {
		$s = isset($_SESSION['search_query']['query']) ? $_SESSION['search_query']['query'] : '';
		return E('form', A('query', $s));
	}

	function display() {
		if (empty($_GET['search'])) return "<form/>";

		$search_query = $_GET['search'];
		$page = isset($_GET["spage"])?(int)$_GET["spage"]:0;
		$page_size = 10;
		
		if(!(isset($_REQUEST["search_id"]) && $_SESSION['search_query']['id'] == $_REQUEST["search_id"])) {
			$this->new_query($search_query, '');
		} //else echo 'use old result';

		if($_SESSION['search_query']['query'] == '') return "<result><query/><error>«адан неверный запрос. ¬ запросе необходимо использовать слова длиннее 2 символов латинского, русского алфавитов</error></result>";
		if((int)$page_size > 50) $page_size = 50;
		
		
		$search = urlencode($_SESSION['search_query']['query']);
		$count_rows = count($_SESSION['search_query']['result']);
		if(!$count_rows) return "<result><query><![CDATA[{$_SESSION['search_query']['query']}]]></query></result>";
		
		$c_pages = (int)(($count_rows + $page_size - 1) / $page_size);
		if($page >= $c_pages) $page = 0;
		
		$start = $page * $page_size;
		$weights = array_slice($_SESSION['search_query']['result'], $start, $page_size);
		$ids = array_slice(array_keys($_SESSION['search_query']['result']), $start, $page_size);
		
		$xml = "<result id='{$_SESSION['search_query']['id']}' page='$page' pagesize='$page_size' pagecount='$c_pages' result='$count_rows'><url><![CDATA[$search]]></url><query><![CDATA[{$_SESSION['search_query']['query']}]]></query>";
		$min = $_SESSION['search_query']['min'];
		
		$q = $this->execSQL("SELECT id, name, url, scrap, topic, changed FROM {$this->tablename} WHERE id IN (" . implode(', ', $ids) . ")");
		while($data = $q->get())
			$row[$data[0]] = $data;
		
		while(list($_, $doc_id) = each($ids)) {
			list($_, $weight) = each($weights);
			list($id, $name, $url, $scrap, $topic, $date) = $row[$doc_id];
			$xml .= "<item href='$url'><topic><![CDATA[$topic]]></topic> <head><![CDATA[$name]]></head><scrap><![CDATA[" . trim(strip_tags($scrap)) . "]]></scrap> <min>" . (int)($min >= $weight) . "</min>	<date>" . substr($date, 8, 2) . '.' . substr($date, 5, 2) . '.' . substr($date, 0, 4) . "</date>	</item>";
		}
		
		$xml .= "<pages page=\"" . ($page + 1) . "\">";
		for($ip = 0; $ip < $c_pages; ++$ip) {
			$xml .= "<page no=\"" . ($ip + 1) . "\"/>";
		}
		$xml .= "</pages>";
		$xml .= "</result>";
		return $xml;
	}
}
?>