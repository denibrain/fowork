<?php
namespace FW\Web;

class Module extends \FW\App\Module {
	
	function form($name) {
		$name = $this->classname.".".strtolower($name);
		if (!file_exists($f = FW_PTH_APP."forms/$name.php"))
			throw new \Exception("Not found form $name ($f)");
			
		$form = new \FW\VCL\Form($name, $f);
		return $form;
	}
}

?>