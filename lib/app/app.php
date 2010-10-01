<?php
namespace FW\App;

class EApp extends \Exception {}

class App extends \FW\Object {
	
	static public $_;
	
	private $db; // main DB connection 
	private $mt; // main Mail Transport
	private $mm; // moduls of application
	private $systemLog;
	private $xslt; // text transformer
	private $txparser;
	private $exparser;
	
	public function run() {
		/* do something */
	}
	
	public function __construct($root = false) {
		self::$_ = $this;

		define('FW_ROOT', $root ? $root : $this->approot);
		if (!defined('FW_PTH_ETC')) define('FW_PTH_ETC', FW_ROOT.'etc/');

		if (file_exists($f = FW_PTH_ETC.'app.cfg.php')) include $f;
		
		// set default settings
		if (!defined('FW_CHARSET')) define('FW_CHARSET', 'utf-8');
		if (!defined('FW_CHARSET2')) define('FW_CHARSET2', 'utf8');
		if (!defined('FW_LANGUAGE')) define('FW_LANGUAGE', 'ru');
		if (!defined('FW_TIMEZONE')) define('FW_TIMEZONE', 'Asia/Yekaterinburg');

		if (!defined('FW_PTH_TEMP')) define('FW_PTH_TEMP', FW_ROOT.'tmp/');
		if (!defined('FW_PTH_DESIGN')) define('FW_PTH_DESIGN', FW_ROOT.'design/');
		if (!defined('FW_PTH_CONTENT')) define('FW_PTH_CONTENT', FW_ROOT.'content/');
		if (!defined('FW_PTH_CACHE')) define('FW_PTH_CACHE', FW_ROOT.'cache/');
		
		if (!defined('FW_PTH_APP')) define('FW_PTH_APP', FW_ROOT.'app/');
		if (!defined('FW_PTH_LOCALE')) define('FW_PTH_LOCALE', FW_PTH_APP.'locale/');
		if (!defined('FW_PTH_COMPONENTS')) define('FW_PTH_COMPONENTS', FW_PTH_APP.'components/');
		if (!defined('FW_PTH_MODULES')) define('FW_PTH_MODULES', FW_PTH_APP.'modules/');
		if (!defined('FW_PTH_DB')) define('FW_PTH_DB', FW_PTH_APP.'db/');
		
		date_default_timezone_set(FW_TIMEZONE);
		set_exception_handler(array($this, "exceptionHandler"));

		// load locale
		if (file_exists(FW_PTH_LOCALE))
		foreach(new \DirectoryIterator(FW_PTH_LOCALE) as $entry)
			if (preg_match('/^'.FW_LANGUAGE.'(\.[a-z]+)?\.php$/', $entry))
				require FW_PTH_LOCALE."$entry";

		if (defined('FW_PTH_LOG'))
			$this->systemLog = new \FW\Log\File('system');
		elseif (defined('FW_TBL_LOG'))
			$this->systemLog = new \FW\Log\Db('system');
		elseif (defined('FW_MAIL_LOG'))
			$this->systemLog = new \FW\Log\Mail('system');
		else
			$this->systemLog = new \FW\Log\Log('system');
		//	$a = new \FW\Log\File('system');

		$this->mm = new ModuleManager($this);
		$this->xslt = new \FW\Text\XSLTransformer(FW_PTH_DESIGN."xsl/");
		$this->txparser = new \FW\Text\Parser(FW_LIB.'/app/stx/caption.php');
		$this->exparser = new \FW\Text\Parser(FW_LIB.'/app/stx/call.php');

		spl_autoload_register(array($this, 'componentLoad'));
	}

	function componentLoad($name) {
		$pos = strpos($name, '\\');
		if ($pos === 0) {
			$name = substr($name, 1);
			$pos = strpos($name, '\\', 1);
		}
		if (false!==$pos) {
			$dName = \strtolower(substr($name, 0, $pos));
			if ($dName ==='page' || $dName ==='grid' || $dName ==='form') {
				$fileName =  FW_PTH_COMPONENTS.\strtolower(\str_replace('\\', '/', $name)).'.php';
				if (file_exists($fileName))
					require $fileName;
			}
		}
	}

	function __destruct() {
	}

	public function exceptionHandler($e) {
		if (!$this->systemLog)
			die("SYSLOG:FAIL WRITE ".$e->getMessage());

		$this->systemLog->write(
			sprintf("[%d] %s\nFile: %s:%d\nStack trace:\n",
				$e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine() 
			).$e->getTraceAsString()
			
		);
	}

	function puresafe($method/* param */)  {
		$params = array_slice(func_get_args(), 1);

		$this->db->begin();
		try {
			$result = call_user_func_array($method, $params);
			$this->db->commit();
		} catch (\Exception $e) {
			$this->db->rollback();
			throw $e;
		}
		return $result;
	}

	public function log($str) {
		$this->systemLog->write($str);
	}
	
	// TODO mail
	function mailMe($message) {
		
	}
	
	function mailTo($text, $topic='Сообщение робота', $to='') {
		$letter = new \FW\Net\MailLetter('', $topic, FW_MAILSITE);

		if (is_array($text)) {
			if (count($text)==1) $letter->html = $text;
			else {
				if (isset($text[1]) && $text[1]!='') $letter->html = $this->transform($text[0], $text[1]);
				if (isset($text[2]) && $text[2]!='') $letter->text = $this->transform($text[0], $text[2]);
			}
		} else $letter->text = $text;

		$letter->to = $to?$to:FW_MAILSITE;
		$letter->subject =$topic;

		$smtp = new \FW\Net\SMTP(FW_MAILHOST);
		$smtp->send($letter);

	}
	
	function transform($e, $name) {
		if (!($e instanceof \FW\Text\Element))
			throw new EApp("Invalid Element for transform");
		return $this->xslt->transform($e->asXML(), $name);
	}
	
	public function __get($key) {
		switch($key) {
			case 'approot': return getcwd().'/';
			case 'db': return !isset($this->db) && defined('FW_DB') ? $this->db = \FW\DB\DB::connect(FW_DB, FW_PTH_DB) : $this->db;
			case 'mm': return $this->mm;
			case 'mt':
				if (!isset($this->mt)) $this->mt = MailTransport::connect(FW_MAIL);
				return $this->mt;
			case 'log': return $this->systemLog;
			default:
				parent::__get($key);
		}
	}

	function call($expr, $params, $prefix = 'display') {
		$h = $params instanceof THCall ? $params : new THCall($params, $prefix);
		$h->init();
		$this->exparser->compile($expr, array($h, 'proceed'));
		return $h->call();
	}

	function content($expr, $params = array(), $prefix = 'display') {
		$h = $params instanceof THCall ? $params : new THCall($params, $prefix);
		$h->init();
		$this->exparser->compile($expr, array($h, 'proceed'));
		return $h->content();
	}
	
	function resolve($expr, $params, $prefix = 'caption') {
		if (false===strpos($expr, '{') && false===strpos($expr, '$')) return $expr;
		$h = new THResolve($params, $prefix);
		$this->txparser->compile($expr, array($h, 'proceed'));
		return $h->text;
	}
}

?>