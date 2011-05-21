<?php
namespace FW\Net;

use \FW\Object as Object;

class EMailLetter extends \Exception {}

//@TODO remove recreate header of email
//@TODO recreate date in header
class MailAddress extends Object {
	private $email;
	private $name;
	
	function __construct($email, $name = '') {
		$this->__set('email', $email);
		$this->__set('name', $name);
	}
	
	function __set($key, $value) {
		switch ($key) {
			case 'email':
				// validate
				if (!\filter_var($value, FILTER_VALIDATE_EMAIL)) 
					throw new EMailLetter("Invalid E-mail");
				$this->email = $value;
				break;
			case 'name':
				$this->name = remle($value);
				break;
			default:
				parent::__set($key, $value);
		}
	}
	
	function __get($key) {
		switch ($key) {
			case 'name':return $this->name;
			case 'email':return $this->email;
			default:
				return parent::__get($key, $value);
		}
	}
	
	function __toString() {
		if ($this->name) return T($this->name)->qencode." <$this->email>";
		else return $this->email;
	}
}

class MailAddressList extends Object {
	private $items;
	
	function __construct() {
		$this->items = array();
	}
	
	function add($a) {
		if (is_object($a) && $a instanceof MailAddressList) {
			foreach($a->items as $key => $addr) {
				$this->items[$key] = $addr;
			}
		}
		else {
			if (!($a instanceof MailAddress))
				$a = new MailAddress((string)$a);
			$this->items[$a->email] = $a;
		}
	}
	
	function set($a) {
		$this->items = array();
		$this->add($a);
	}
	
	function __get($key) {
		switch ($key) {
			// @TODO hide this
			case 'items': return $this->items;
			case 'count':return count($this->items);
			default:
				return parent::__get($key);
		}
	}
	
	function __toString() {
		$items = array();
		foreach($this->items as $a) $items[$a->email] = $a;
		$this->items = $items;
		return implode(";".PHP_EOL."\t", $items);
	}
}

class MailContent extends Object {
	private $headers;
	private $body;
	private $boundary;
	private $encode;
	private $id;
	
	function __construct($contentType, $enCode = 'base64') {
		$this->body = '';
		$this->headers = new Header();
		$this->boundary = md5(microtime());
		$this->encode = $enCode;
		$this->id = md5(microtime());
		if ($contentType) {
			$this->headers->add('Content-Type', $contentType);
		}
		$this->headers->add('Content-Transfer-Encoding', $enCode);
	}

	function __get($key) {
		switch ($key) {
			case 'headers':return $this->headers;
			case 'body': return $this->getBody();
			case 'id' : return $this->id;
			case 'boundary' : return $this->boundary;
			default: return parent::__get($key);
		}
	}
	
	public function __set($key, $value) {
		switch ($key) {
			case 'id' :
				$this->id = (string)$value;
				break;
			case 'body':
				$this->body = (string)$value;
				break;
 			default:
				parent::__set($key, $value);

		}
	}
	
	public function combine() {
		$a = func_get_args();
		$this->body = array();
		foreach($a as $item) {
			if (is_object($item) && $item instanceof MailContent) {
				$this->body[] = $item;
			}
			else if(is_array($item)) {
				foreach($item as $subitem) {
					if (is_object($item) && $item instanceof MailContent) {
						$this->body[] = $item;
					}
					else {
						$this->body[] = $i = new MailContent('text/plain');
						$i->body = (string)$subitem;
					}
				}
			}
			else {
				$this->body[] = $i = new MailContent('text/plain');
				$i->body = (string)$item;
			}
		}
	}
	
	private function getBody() {
		if (is_array($this->body)) {
			$this->headers->Content_Type->boundary = $this->boundary;
			$body = (string)$this->headers.PHP_EOL;
			foreach($this->body as $item) 
				$body .= "--$this->boundary".PHP_EOL.$item.PHP_EOL;
			$body .= "--$this->boundary--".PHP_EOL;
			return $body;
		} else {
			return (string)$this->headers.$this->encode($this->body);
		}
	}
	
	private function encode($str) {
		switch($this->encode) {
			case 'base64':
				return chunk_split(base64_encode($str), 76, PHP_EOL);
			case '8bit':
				$str = fixle($str);
				if (substr($str, -strlen(PHP_EOL)) !== PHP_EOL) $str .= PHP_EOL;
				return str_replace("\n.\n", "\n..\n", wordwrap($str, 76, PHP_EOL, true));
			case 'quoted-printable':
				return str_replace("\n.\n", "\n..\n", quoted_printable_encode($str));
			default:
				throw EMail('undefined enocidng');
		}
	}

	public function __toString() {
		return $this->getBody();
	}
	
}

class MailLetter extends Object {
	private $body = false;
	private $headers = array();
	private $inlineImages = false;
	private $attaches = array();
	
	private $userHeaders;
	
	private $to;
	private $cc;
	private $bcc;
	private $replayTo;
	private $from;

	private $hostname;
	private $confirm;
	
	private $changed = false;

	private $priority = 3; // Email priority (1 = High, 3 = Normal, 5 = low).
	private $subject = 'W/o subject';
	private $sender = ''; // Sets the Sender email of the message. If not empty, will be sent via -f to sendmail or as 'MAIL FROM' in smtp mode.
	private $MessageID = '';
	private $encode = FW_MAILENCODE;
	
	private $text;
	private $html;
	private $params = array();
	
	private $cache;
	
	private $sign_cert_file;
	private $sign_key_file;
	private $sign_key_pass;
	
	function __construct($text = '', $subject = '', $from = '') {
		$this->to = new MailAddressList();
		$this->bcc = new MailAddressList();
		$this->cc = new MailAddressList();
		$this->replayTo = new MailAddressList();
		$this->headers = false;
		$this->userHeaders = new Header();
		
		$this->hostname = (isset($_SERVER['SERVER_NAME']) ?
			$_SERVER['SERVER_NAME'] : 
			'localhost.localdomain');		
		$this->MessageID = "<".md5(microtime())."@$this->hostname>";
		
		$this->from = new MailAddress($from?$from:FW_MAIL);
		if ($text) $this->__set('text', $text);
		$this->__set('subject', $subject?$subject:FW_MAILSUBJECT);
	}
	
	public function __set($member, $value) {
		switch($member) {
			case 'to':
				$this->to->add($value);
				$this->changed |= 8;
				break;
			case 'cc':
				$this->cc->add($value);
				$this->changed |= 8;
				break;
			case 'bcc':
				$this->bcc->add($value);
				$this->changed |= 8;
				break;
			case 'replayTo':
				$this->replayTo->add($value);
				$this->changed |= 8;
				break;
			case 'from':
				if (is_object($value) && ($value instanceof MailAddress))
					$this->from = $value;
				else
					$this->from = new MailAddress((string)$value);				
				$this->changed |= 8;
				break;
			case 'priority':
				$this->priority = ((int)$value)%6;
				$this->changed |= 8;
				break;
			case 'subject':
				// @TODO need check
				$this->subject = remle($value);
				$this->changed |= 8;
				break;
			case 'message_id':
				// @TODO need check
				$this->MessageID = remle($value);
				$this->changed |= 8;
				break;
			case 'text':
				// @TODO need check
				$this->text = $value;
				$this->changed |= 1;
				break;
			case 'html':
				// @TODO need check
				$this->setHtml($value);
				$this->changed |= 2;
				break;
			default:
				parent::__set($member, $value);
		}
	}
	
	public function __get($member) {
		switch ($member) {
			case 'to':return $this->to;
			case 'from':return $this->from;
			case 'text':return $this->text;
			case 'html':return $this->html;
			case 'sender': return $this->sender;
			case 'body':
				if ($this->body === false || $this->changed & 7) $this->createBody();
				return $this->body;
			case 'headers':
				if ($this->headers === false || $this->changed & 8) $this->createHeader();
				return $this->headers;
			case 'allRecipients':
				$al = new MailAddressList;
				$al->add($this->to);
				$al->add($this->cc);
				$al->add($this->bcc);
				return $al;
			default:
				parent::__get($member);
		}
	}
	
	public function attachFile($filename) {
		$this->attaches[] = $this->attachf($filename);
		$this->changed |= 4;
	}
	
	public function attachData($name, $data, $contentType) {
		$this->attaches[] = $this->attach($name, $data, $contentType);
		$this->changed |= 4;
	}	
	
	public function send($to = '') {
		if ($to) $this->to->set[] = $to;
		switch(@parse_url(FW_MAILHOST, 'scheme')) {
			case 'ssl':
			case 'smtp':
				$transport = new SMTP(FW_MAILHOST);
				break;
			case 'mail':
				$transport = MTMail();
				break;
			case 'sendmail':
				$transport = MTSendMail();
				break;
		}
		$transport->send($this);
	}

	public function sign($certFilename, $keyFilename, $keyPass) {
		$this->sign_cert_file = $certFilename;
		$this->sign_key_file = $keyFilename;
		$this->sign_key_pass = $keyPass;
	}

	public function __toString() {
		try {
		
		return //$this->__get('headers').
			(string)$this->__get('body');

		} catch (EMailLetter $e){
			echo $e->getMessage();
		}
	}
	
	private function encodeHeadedr($string) {
		return mb_encode_mimeheader(remle($string), $this->charset, 'Q');
	}

	private function createHeader() {
		$h = new Header();

		$h->addDate();
		$h->Return_Path = trim($this->sender == ''?$this->from:$this->sender);
		$h->From = $this->from;

		if($this->to->count) $h->add('To', $this->to);
		else $h->add('To', FW_MAIL);
		
		
		if($this->cc->count) $h->add('Cc', $this->cc);
		if($this->bcc->count) $h->add('Bcc', $this->cc);
		if($this->replayTo->count) $h->add('Reply-to', $this->replayTo);

		$h->Subject = $this->subject;
		$h->Message_ID = $this->MessageID;
		$h->X_Priority = $this->priority;
		$h->X_Mailer = 'FWmail';

		if($this->confirm != '') 
			$h->add('Disposition-Notification-To', '<' . trim($this->confirm) . '>');
	
		$h->add($this->userHeaders);
		
		if (!$this->sign_key_file) 
			$h->add('MIME-Version', '1.0');
							
		return $this->headers = $h;
	}

	private function createBody() {
		$parts = 0;
		if ($this->text) {
			if ($this->params || $this->changed & 1 || !isset($this->cache['text'])) {
				$t = $this->cache['text'] = new MailContent('text/plain', $this->encode);
				$t->headers->Content_Type->charset = FW_CHARSET;
				$t->body = $this->params ? $this->applyParams($this->text) : $this->text;
			}
			$parts++;
		}

		if ($this->html)  {
			if ($this->params || $this->changed & 2 || !isset($this->cache['html'])) {
				$t = $this->cache['html'] = new MailContent('text/html', $this->encode);
				$t->headers->Content_Type->charset = FW_CHARSET;
				$t->body = $this->params ? $this->applyParams($this->html) : $this->html;
			}
			$parts++;
		}

		if ($parts == 0) $text = new MailContent('text/plain', $this->encode);
		else
		if ($parts == 2) {
			$text = new MailContent('multipart/alternative');
			$text->combine($this->cache['text'], $this->cache['html']);
		}
		else
			$text = $this->cache[$this->text? 'text':'html'];
			
		if ($this->attaches) {
			if ($this->inlineImages) {
				$body = new MailContent('multipart/related');
				$body->headers->Content_Type
					->property('type', "text/html")
					->property('boundary', $body->boundary);
				$body->combine($text, $this->inlineImages, $this->attaches);
			}
			else {
				$body = new MailContent('multipart/mixed');
				$body->combine($text, $this->attaches);
			}
		}
		else
			$body = $text;
			
		// return $body

		if ($this->sign_key_file) {
			try {
				$file = tempnam('', 'mail');
				file_put_contents($file, $body); //TODO check this worked
				$signed = tempnam("", "signed");
				if (@openssl_pkcs7_sign($file, $signed, "file://".$this->sign_cert_file, array("file://".$this->sign_key_file, $this->sign_key_pass), NULL)) {
					@unlink($file);
					@unlink($signed);
					$body = file_get_contents($signed);
				} else {
					@unlink($file);
					@unlink($signed);
					throw new EMail($this->Lang("signing").openssl_error_string());
				}
			} catch (EMailLetter $e) {
				$body = '';
				if ($this->exceptions) {
					throw $e;
				}
			}
		}

		$body->headers->prepend($this->createHeader());
		$this->body = $body;
		
	}

	private function setHtml($value, $basedir = '') {
		$this->inlineImages = array();

		preg_match_all("/(src|background)=\"(.*)\"/Ui", $value, $images);
		if(isset($images[2])) {
			foreach($images[2] as $i => $url) {
				if (!preg_match('#^[A-z]+://#',$url)) {
					$filename = basename($url);
					$directory = dirname($url);
					if ($directory == '.') $directory='';
					if ($basedir && substr($basedir,-1) != '/') $basedir .= '/'; 
					if ($directory && substr($directory,-1) != '/') $directory .= '/';

					$id = md5($filename);
					$images[2][$i] = "{$images[1][$i]}=\"cid:$id\"";
					$this->inlineImages[] = $this->attachf($basedir.$directory.$filename, $id);
				}
			}
			$value = str_replace($images[0], $images[2], $value);
		}
		$this->html = $value;
	}
	
	private function attach($name, $data, $contentType, $inlineID=false) {
		$content = new MailContent($contentType);
		$content->headers->Content_Type->name = $name;

		if($inlineID) 
			$content->headers->Content_ID =  "<$inlineID>";
		$content->headers->Content_Disposition = $inlineID?'inline':'attachment';
		$content->headers->Content_Disposition->filename=$name;
		$content->body = $data;
		return $content;
	}

	private function attachf($filename, $inlineID=false) {
		if (!file_exists($filename)) 
			throw new EMail("File $filename not found");
		
		return $this->attach(
			basename($filename),
			file_get_contents($filename),
			$this->mimeType(substr($filename, strrpos($filename, '.')+1)),
			$inlineID
		);
	}

	private function applyParams($text) {
		$t = ParametricTemplate();
		$t->setText($text);
		return $t->compile($this->params);
	}

	private static function mimeType($ext = '') {
		$mimes = array(
			'hqx'   =>  'application/mac-binhex40',
			'cpt'   =>  'application/mac-compactpro',
			'doc'   =>  'application/msword',
			'bin'   =>  'application/macbinary',
			'dms'   =>  'application/octet-stream',
			'lha'   =>  'application/octet-stream',
			'lzh'   =>  'application/octet-stream',
			'exe'   =>  'application/octet-stream',
			'class' =>  'application/octet-stream',
			'psd'   =>  'application/octet-stream',
			'so'    =>  'application/octet-stream',
			'sea'   =>  'application/octet-stream',
			'dll'   =>  'application/octet-stream',
			'oda'   =>  'application/oda',
			'pdf'   =>  'application/pdf',
			'ai'    =>  'application/postscript',
			'eps'   =>  'application/postscript',
			'ps'    =>  'application/postscript',
			'smi'   =>  'application/smil',
			'smil'  =>  'application/smil',
			'mif'   =>  'application/vnd.mif',
			'xls'   =>  'application/vnd.ms-excel',
			'ppt'   =>  'application/vnd.ms-powerpoint',
			'wbxml' =>  'application/vnd.wap.wbxml',
			'wmlc'  =>  'application/vnd.wap.wmlc',
			'dcr'   =>  'application/x-director',
			'dir'   =>  'application/x-director',
			'dxr'   =>  'application/x-director',
			'dvi'   =>  'application/x-dvi',
			'gtar'  =>  'application/x-gtar',
			'php'   =>  'application/x-httpd-php',
			'php4'  =>  'application/x-httpd-php',
			'php3'  =>  'application/x-httpd-php',
			'phtml' =>  'application/x-httpd-php',
			'phps'  =>  'application/x-httpd-php-source',
			'js'    =>  'application/x-javascript',
			'swf'   =>  'application/x-shockwave-flash',
			'sit'   =>  'application/x-stuffit',
			'tar'   =>  'application/x-tar',
			'tgz'   =>  'application/x-tar',
			'xhtml' =>  'application/xhtml+xml',
			'xht'   =>  'application/xhtml+xml',
			'zip'   =>  'application/zip',
			'mid'   =>  'audio/midi',
			'midi'  =>  'audio/midi',
			'mpga'  =>  'audio/mpeg',
			'mp2'   =>  'audio/mpeg',
			'mp3'   =>  'audio/mpeg',
			'aif'   =>  'audio/x-aiff',
			'aiff'  =>  'audio/x-aiff',
			'aifc'  =>  'audio/x-aiff',
			'ram'   =>  'audio/x-pn-realaudio',
			'rm'    =>  'audio/x-pn-realaudio',
			'rpm'   =>  'audio/x-pn-realaudio-plugin',
			'ra'    =>  'audio/x-realaudio',
			'rv'    =>  'video/vnd.rn-realvideo',
			'wav'   =>  'audio/x-wav',
			'bmp'   =>  'image/bmp',
			'gif'   =>  'image/gif',
			'jpeg'  =>  'image/jpeg',
			'jpg'   =>  'image/jpeg',
			'jpe'   =>  'image/jpeg',
			'png'   =>  'image/png',
			'tiff'  =>  'image/tiff',
			'tif'   =>  'image/tiff',
			'css'   =>  'text/css',
			'html'  =>  'text/html',
			'htm'   =>  'text/html',
			'shtml' =>  'text/html',
			'txt'   =>  'text/plain',
			'text'  =>  'text/plain',
			'log'   =>  'text/plain',
			'rtx'   =>  'text/richtext',
			'rtf'   =>  'text/rtf',
			'xml'   =>  'text/xml',
			'xsl'   =>  'text/xml',
			'mpeg'  =>  'video/mpeg',
			'mpg'   =>  'video/mpeg',
			'mpe'   =>  'video/mpeg',
			'qt'    =>  'video/quicktime',
			'mov'   =>  'video/quicktime',
			'avi'   =>  'video/x-msvideo',
			'movie' =>  'video/x-sgi-movie',
			'doc'   =>  'application/msword',
			'word'  =>  'application/msword',
			'xl'    =>  'application/excel',
			'eml'   =>  'message/rfc822'
		);
		return (!isset($mimes[strtolower($ext)])) ? 'application/octet-stream' : $mimes[strtolower($ext)];
	}

}
?>