<?php

class ErrorPage extends \FW\Web\Module {
	
	function error($code, $message) {
		$ds = $this->dsCode(array('code'=>$code));
		if (list($url) = $ds->get()) {
			\FW\App\App::$_->request->url->address = $url;
			$content = $this->app->mm->page->compile(new \FW\Web\URL($url));
		} else {
			$content = new \FW\Web\Content();
			$content->body = $message;
		}
		$content->code = $code;
		return $content;
	}
}
?>