<?php
namespace FW\IO;
/**
 * Description of link
 *
 * @author d.russkih
 */
class Link extends FileSystemItem {

	function delete() {
		unlink($this->name);
	}

	function copyTo($name) {
		$source = readlink($item->name);
	   \symlink($source, $item->name);
	}
}
?>
