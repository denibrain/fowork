<?php
namespace FW\Web;

class Module extends \FW\App\Module {

	protected $item = false;
	
	function __call($name, $args) {
		if (substr($name, 0, 7) === 'display') {
			$pageName = substr($name, 7);
			if (!$pageName) $pageName = $this->classname;
			$pageClass = "\\Page\\$this->classname\\$pageName";
			return $this->defaultDisplay($pageClass, $pageName, $args[0]);
		}
		elseif (substr($name, 0, 7) === 'content') {
			$contentName = substr($name, 7);
			if (!$contentName) $contentName = $this->classname;
			$contentClass = "\\Component\\$this->classname\\$contentName";
			return $this->defaultContent($contentClass, $contentName, $args[0]);
		}
		elseif (substr($name, 0, 7) === 'caption') {
			$pageName = substr($name, 7);
			if (!$pageName) $pageName = $this->classname;
			$pageClass = "\\Page\\$this->classname\\$pageName";
			return $this->defaultCaption($pageClass, $pageName, $args[0]);
		}
		else return parent::__call ($name, $args);
	}

	function getItem($params) {
		if (!$this->item || $this->item['id'] == $params['id']) {
			if (!($this->item = $this->dsItem($params)->getA())) throw new \E404();
		}
		return $this->item;
	}

	function defaultDisplay($pageClass, $name, $params) {
		$db = \FW\App\App::$_->db;
		$db->begin();
		try {
			if ($_SERVER['REQUEST_METHOD'] != 'GET' && isset($_SESSION['page']) && $_SESSION['page']['url'] == App::$_->request->url) {
				$page = unserialize($_SESSION['page']['data']);
			} else {
				$page = new $pageClass($name);
				$page->init($params);
			}

			$page->run();

			$_SESSION['page'] = array(
				'url' => App::$_->request->url,
				'data' => serialize($page)
			);

			$result = $page->display();
			
			$db->commit();
		} catch (\ERequest $e) {
			$db->commit();
			throw $e;
		}
		catch (\Exception $e) {
			$db->rollback();
			$result = E('error', A('msg', $e->getMessage()));
			throw $e;
		}
	
		return $result;
	}

	function defaultContent($contentClass, $name, $params) {
		$db = \FW\App\App::$_->db;
		$db->begin();

		$content = new $contentClass($name);

		try {
			$content->init($params);
			$result = $content->display();

			$db->commit();
		} catch (\ERequest $e) {
			$db->commit();
			throw $e;
		}
		catch (\Exception $e) {
			$db->rollback();
			$result = E('error', A('msg', $e->getMessage()));
			throw $e;
		}

		return $result;
	}

	function defaultCaption($pageClass, $name, $params) {
		$page = new $pageClass($name);
		return $page->caption($params);
	}

	function defaultMap($pageClass, $name, $params) {
		$page = new $pageClass($name);
		return $page->map($params);
	}
}