<?php
namespace FW\Text;

class Transformer extends \FW\Object {
	
	private $file;
	
	function __construct($transform) {
		$this->file = FW_PTH_CACHE."transforms/$transform.php";
		if (!file_exists($file)) {
			$compiler = new TransformCompiler();
			$source = FW_PTH_DESIGN."transforms/$transform.tfm";
			$compiler->compile($sourice, $this->file);
		}
	}

	function transform($e) {
		$html = new Html();
		ob_start();
		include $f;
		$html->data = ob_get_contents();
		ob_end_clean();
		return $html;
	}
}

?>