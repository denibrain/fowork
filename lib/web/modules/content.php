<?php
class Content extends \FW\App\Module {
	
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

		$content = new \FW\Web\Content('html');
		
		$this->app->checkLevel($acLevel);
		$h = new \FW\App\THCall($this->params, 'content');
		try {
			if ($expression) {
				$body = $this->app->content($expression, $h);
			}
			else $body = '';
		}
		catch (ERequest $e) {
			throw $e;
		}
		catch (Exception $e) {
			$content->code = 400;
			$body = $e->getMessage();
		}
	
		$content->body = $body;
		return $content;
	}
}

?>