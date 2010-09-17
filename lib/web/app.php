<?php
namespace FW\Web {
	
	/*
	 @property Request $request Request info
	*/
	class App extends \FW\App\App {
	
		private $request;
		private $cache;
		private $session;
		private $user;

		private $activeContainer;
		
		function __construct($root = false) {
			parent::__construct($root);
			$this->mm->addPath(dirname(__FILE__).'/modules/');

			if (defined('FW_PTH_TEMP'))
				ini_set('session.save_path', FW_PTH_TEMP);
			session_start();  // TODO remove and use session module

		}
		
		/* проверка прав текущего пользвоателя
		  @param $permissions строка прав
		  @return bool: true 
		*/
		public function checkLevel($aclevel) {
			if (''==$aclevel) return true;
			if (!in_array($aclevel, $this->user->groups)) {
				if ($this->user->id) throw new \E403();
				else throw new \E401();
			}
		}
		
		/* обработка запросов на файлы
		  @param $url: Full url co/....
		  @return: Content 
		*/
		function proceedFile($url) {
			if (count($url->domain)!=4)	throw new E400();
			$content = new File(FW_PTH_CONTENT.$url);
			if (!$content->exists) throw new E404();
			array_shift($url->domain);

			/* проверяем разрешения для показа файла пользователю */
			$ds = new DataSet('fileperms', array('u'=>(string)$url));
			$permited = defined(FW_ANYPUBLIC);
			if ($hdl = $ds->getA()) {
				$perimssions = $this->call($data['permitter'],
					array_slice($url->domain, 1));
				$permited = $this->user->checkPermissions($perimssions);
			}
			if (!$permited)  {
				if ($this->user->id) throw new E403();
				else throw new E401();
			}
			return $content;
		}
		
		function proceedError(\ERequest $e) {
			$this->activeContainer = $this->mm->Page;
			return $this->mm->errorpage->error($e->getCode(), $e->getMessage());
		}
	
		function run() {
			try {
				try {
					\ob_start();
					$this->request = new Request();
					$this->user = $this->mm->user;
					
					$url = $this->request->url;
					$d = $url->domain[0];
					if ($d == 'co') $content = $this->proceedFile($url);
					elseif ($d == 'cmd') {
						$this->activeContainer = $this->mm->Command;
						$content = $this->activeContainer->compile($url->Local(1));
					}
					elseif ($d == 'txt') {
						$this->activeContainer = $this->mm->Content;
						$content = $this->activeContainer->compile($url->Local(1));
					}
					else {
						$this->activeContainer = $this->mm->Page;
						$content = $this->activeContainer->compile($url);
					}
					$text = \ob_get_contents();
					$content->body = $text.$content->body;
					\ob_end_clean();
					$content->send();
				}
				catch (\ERequestError $e) {
					\ob_end_flush();
					$this->proceedError($e)->send();
				}
			}
			catch (\ERedirect $e){
				$content = $this->proceedError($e);
				$content->headers->Location = $e->url;

				$text = ob_get_contents();
				$content->body = $text.$content->body;
				ob_end_clean();
				
				$content->send();
			}
		}
	
		public function __get($key) {
			switch($key) {
				case 'approot': return $_SERVER['DOCUMENT_ROOT'].'/';
				case 'request': return $this->request;
				case 'user': return $this->user;
					
				// for future CSS, JS 
				case 'activeContainer': return $this->activeContainer;
				default: return parent::__get($key);
			}
		}
	
		function exceptionHandler($e) {
			echo sprintf(
				"<p><strong style='color:#600'>[%d] %s\nFile: %s:%d</strong></p>".
				"<p>Stack trace:</p>", 
				$e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
		
			echo "<pre><table>".
				preg_replace(
					array(
						"/^/m",
						"/\[internal function\]:/",
						"/$/m",
						"/([a-z.]+php)/", 
						"/\\(([0-9]+)\\):/"
					),
					array(
						"<tr><td>",
						"</td><td></td><td align='center'>&ndash;</td><td>",
						"</td></tr>",
						"</td><td><strong style='color:green'>$1</strong></td>" ,
						"<td style='padding:0 10px; text-align:right'><strong style='color:red'>$1</strong></td><td>"
					), 
					he($e->getTraceAsString())
				).
				"</table></pre>";
		}
		
		function transform($e, $name) {
			$e->baseurl = (string)$this->request->url;
			$e->parenturl = implode('.', array_slice($this->request->url->domain, 0, -1));
			return parent::transform($e, $name);
		}
	}
}

namespace  {

	class ERequest extends \Exception {}

	class ERequestError extends ERequest {}
	
	class ERedirect extends ERequest {
		public $url;
		function __construct($url) { $this->url = $url; parent::__construct('Moved Permanently', 302);	}
	}

	class E400 extends ERequestError {function __construct() {parent::__construct('Bad Request', 400); }}
	class E401 extends ERequestError {function __construct() {parent::__construct('Unauthorized', 401); }}
	class E403 extends ERequestError {function __construct() {parent::__construct('Forbidden', 403); }}
	class E404 extends ERequestError {function __construct() {parent::__construct('Not Found', 404); }}
}
?>