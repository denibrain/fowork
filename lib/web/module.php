<?php
namespace FW\Web;

class Module extends \FW\App\Module {
	
	function form($name = '') {
		$name = $this->classname.($name ? ".".strtolower($name) : '');
		if (!file_exists($f = FW_PTH_APP."forms/$name.php"))
			throw new \Exception("Not found form $name ($f)");
			
		$form = new \FW\VCL\Forms\Form($name, $f);
		return $form;
	}
	
	function grid($datasource = NULL, $name = '') {
		$name = $this->classname. ($name ? ".".strtolower($name) : '');
		if (!file_exists($f = FW_PTH_APP."grids/$name.php"))
			throw new \Exception("Not found grid $name ($f)");
			
		$form = new \FW\VCL\Grid\Grid($name, $f);
		if ($datasource) $form->dataSource = $datasource;
		return $form;
	}	
}

?>