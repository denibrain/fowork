<?php
namespace FW\Web;

class Command extends \FW\Object {
	
	protected $permissions;
	
	static function factory(URL $url) {
		if (count($url->domain)<2)	throw new E400();
		array_shift($url->domain);
		$ds = new DataSet('command', array('id'=>$url->domain[1]));
		if (!(list($cmd) = $ds->get())) throw new E404();
		
		$cmd = 'Command\\'.$cmd;
		$command = new $cmd();
	}
	
	function __construct($url) {
		$this->permissions = '';
	}
	
	function execute($params) {
		
	}
	
	function __get($key) {
		switch ($key) {
			case 'permissions' : return $this->permissions;
			default: return parent::__get($key);
		}
	}
}

?>