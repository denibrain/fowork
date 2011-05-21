<?php
class Command extends \FW\App\Module {
	
	private $id;
	private $params;
	private $tablename = FW_TBL_COMMAND;
	
	function __call($name, $args) {
		if (substr($name, 0, 2) == 'ds') $args[0]['table'] = $this->tablename;
		return parent::__call($name, $args);
	}
		
	function compile($url) {
		if (count($url->domain)<1)
			throw new E400();

		$this->id = $url->mask;
		$this->params = array_reverse($url->domain);

		if (!(list($expression, $acLevel) = $this->dsActive(array('id'=>$this->id))->get()))
			throw new E404();
		
		$this->app->checkLevel($acLevel);
		$h = new \FW\App\THCall($this->params, 'command');
		try {
			if ($expression) {
				$status = $this->app->call($expression, $h);
			}
			else $status = 'empty';
			$result = E('response', A('status', $status));
		}
		catch (ERequest $e) {
			throw $e;
		}
		catch (Exception $e) {
			$result = E('response', A('status', 'error', 'message', $e->getMessage(), 'code', $e->getCode()));
		}
	
		$content = new \FW\Web\Content('json');
		$content->body = $result->asJSON();
		return $content;
	}
}

?>