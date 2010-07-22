<?php
namespace FW\Web;

class Module extends \FW\App\Module {
	
	function form($name = '') {
		$name = $this->classname.($name ? ".".strtolower($name) : '');
		if (!file_exists($f = FW_PTH_APP."forms/$name.php"))
			throw new \Exception("Not found form $name ($f)");
			
		$form = new \FW\VCL\Forms\Form($name, $f, $this);
		if ($form->autoProceed) $form->proceed();
		return $form;
	}
	
	function grid($datasource = NULL, $name = '') {
		$name = $this->classname. ($name ? ".".strtolower($name) : '');
		if (!file_exists($f = FW_PTH_APP."grids/$name.php"))
			throw new \Exception("Not found grid $name ($f)");
			
		$form = new \FW\VCL\Grid\Grid($name, $f, $this);
		if ($datasource) $form->dataSource = $datasource;
		return $form;
	}
	
	function safe($method/* param */)  {
		try {
			return call_user_func_array(array($this, 'puresafe'), func_get_args());	
		} catch (Exception $e) {
			return E('error', A('msg', $e->getMessage()));
		}
	}
	
	function puresafe($method/* param */)  {
		$params = array_slice(func_get_args(), 1);
		
		App::$_->db->begin();
		try {
			$result = call_user_func_array(array($this, $method), $params);
			App::$_->db->commit();
		} catch (\Exception $e) {
			App::$_->db->rollback();
			throw $e;
		}
		return $result;
	}

}

?>