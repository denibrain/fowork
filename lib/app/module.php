<?php
namespace FW\App;

class Module extends Entity {

	static function _() {
		$m = strtolower(\get_called_class());
		return \FW\App\App::$_->mm->$m;
	}
}
?>