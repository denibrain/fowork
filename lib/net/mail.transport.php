<?php
namespace FW\Net;

use \FW\Object as Object;

class MailTransport {
}

class MTSendMail extends MailTransport {
	private $command = '/usr/sbin/sendmail';
	// Sends mail using the $Sendmail program.
	
	function __construct($cmd = 'sendmail') {
		if ($cmd == 'sendmail')	{
			if (!stristr(ini_get('sendmail_path'), 'sendmail'))
				$this->command = '/var/qmail/bin/sendmail';
		}
		elseif ($cmd == 'qmail')	{
			if (!stristr(ini_get('sendmail_path'), 'qmail')) 
				$this->command = '/var/qmail/bin/sendmail';
		}
		if ($cmd)
			$this->command = $cmd;
		else
			$this->command = ini_get('sendmail_path');
	}
	
	public function send($letter) {
		if ($this->sender != '') {
			$sendmail = sprintf("-oi -f %s -t", escapeshellarg($this->Sender));
		} else {
			$sendmail = " -oi -t";
		}
		if(!@$mail = popen(escapeshellcmd($this->command).$sendmail, 'w')) {
			throw new EMail("Cannot execute $this->command");
		}
		fputs($mail, $letter);
		if(pclose($mail) != 0)
			throw new EMail("Cannot execute $this->command");
	}	
}

class MTMail {
	public function send($letter) {
		$params = sprintf("-oi -f %s", $letter->sender);
		if ($this->sender != '' && strlen(ini_get('safe_mode'))< 1) {
			$old_from = ini_get('sendmail_from');
			ini_set('sendmail_from', $letter->sender);
		}
		
		if ($letter->singleTo) {
			foreach($letter->to as $to) {
				$rt = @mail($to, $this->subject,
							$letter->body, $letter->header, $params);
			}
		} else {
				$rt = @mail($letter->to, $this->subject,
							$letter->body, $letter->header, $params);
		}
		if (isset($old_from)) {
			ini_set('sendmail_from', $old_from);
		}
		if(!$rt) {
			throw new EMail($this->Lang('instantiate'), self::STOP_CRITICAL);
		}
		return true;
	}	
}

class POP3 {
	public $POP3_PORT = 110;
	public $POP3_TIMEOUT = 30;
	public $CRLF = "\r\n";

	public $host;
	public $port;
	public $tval;
	public $username;
	public $password;

	private $pop_conn;
	private $connected;

	public function __construct() {
		$this->pop_conn  = 0;
		$this->connected = false;
	}

	// Combination of public events - connect, login, disconnect
	public function Authorise ($host, $port = false, $tval = false, $username = '', $password = '') {
		$this->host = $host;
		$this->port = $port ? $port : $this->POP3_PORT;
		$this->tval = $tval ? $tval : $this->POP3_TIMEOUT;
		$this->Connect($this->host, $this->port, $this->tval);
		$this->Login($this->username, $this->password);
		$this->Disconnect();
	}

	// Connect to the POP3 server
	public function Connect ($host, $port = false, $tval = 30) {
		if ($this->connected) return true;

		//  Connect to the POP3 server
		$this->pop_conn = @fsockopen($host,    //  POP3 Host
			$port,    //  Port #
			$errno,   //  Error Number
			$errstr,  //  Error Message
			$tval);   //  Timeout (seconds)

		if ($this->pop_conn == false) 
			throw EPOP("Failed to connect to server $host on port $port ($errno, $errstr)");

		//  Does not work on Windows
		if (substr(PHP_OS, 0, 3) !== 'WIN') 
			socket_set_timeout($this->pop_conn, $tval, 0);

		$this->response();
		return $this->connected = true;
	}

	// Login to the POP3 server (does not support APOP yet)
	public function Login ($username = '', $password = '') {
		if ($this->connected == false) 
			throw new EPOP('Not connected to POP3 server');

		$this->send("USER ".($password?$password:$this->password).PHP_EOL);
		$this->send("PASS ".($username?$username:$this->username).PHP_EOL);
	}

	public function Disconnect () {
		$this->send('QUIT');
		fclose($this->pop_conn);
		$this->connected = false;
	}

	private function send($string) {
		$w = fwrite($this->pop_conn, $string, strlen($string));
		$this->response();
		return $w;
	}
	
	private function response() {
		$response = fgets($this->pop_conn, $size);
		if (substr($string, 0, 3) !== '+OK') 
			throw EPOP("Server reported an error: $response");
	}
}

class ESMTP extends \Exception {
	function __construct($message, $code = 0) {
		parent::__construct($message, $code);
	}
}

class SMTPReplay {
	public $code;
	public $message;

	function __construct($raw) {
		$this->code = substr($raw, 0, 3);
		$this->message = substr($raw, 4);
	}
}
 
class SMTP extends Object {
	public $SMTPSecure = '';
	public $Helo;

	private $handle; // the socket to the server
	private $helo_rply; // the reply the server sent to us for HELO
	private $log;
	
	public function __construct($connectTo = false) {
		$this->handle = 0;
		$this->Helo = (isset($_SERVER['SERVER_NAME']) ? 
			$_SERVER['SERVER_NAME'] : 
			'localhost.localdomain');
		$this->helo_rply = null;
		$this->log = new \FW\Util\Log('smtp');

		if ($connectTo) $this->Connect($connectTo);
	}

	public function __destruct() {
		if ($this->connected) $this->close();
	}

	public function __get($key) {
		switch ($key) {
			case 'connected':
				if(!empty($this->handle)) {
					$sockStatus = socket_get_status($this->handle);
					if(!$sockStatus["eof"]) return true;
					// the socket is valid but we are not connected
					fclose($this->handle);
					$this->handle = 0;
				}
				return false;
			default:
				parent::__get($key);
		}
	}

	public function checkConnect() {
		if(!$this->connected) 
			throw new ESMTP("Требуется соедниеие");
	}

	public function connect($url = FW_MAILHOST, $tval = 30) {
		// make sure we are __not__ connected
		if($this->connected) 
			throw new ESMTP("Already connected to a server");

		$url = parse_url($url);
		// @TODO check default
		$host = $url['host'];

		$ssl = $tls = false;
		if (isset($url['base'])) {
			$ssl = $url['base']=='ssl';
			$tls = $url['base']=='tls';
		}
		
		if ($ssl) $host = "ssl://$host";
		$port = isset($url['port'])? $url['port']:($ssl & $tls ? 465 : 25);

		// connect to the smtp server
		$this->handle = @fsockopen($host,    // the host of the server
								 $port,    // the port to use
								 $errno,   // error number if any
								 $errstr,  // error message if any
								 $tval);   // give up after ? secs
		// verify we connected properly
		if(empty($this->handle)) 
			throw new ESMTP("Failed to connect to server : $errstr", $errno);
			
		// SMTP server can take longer to respond, give longer timeout for first read
		// Windows does not have support for this timeout function
		if(substr(PHP_OS, 0, 3) != "WIN")
			socket_set_timeout($this->handle, $tval, 0);

		// get any announcement
		$this->recieve();
		$this->hello();

		if ($tls) {
			$this->StartTLS();
			$this->hello();
		}
		
		if (isset($url['user']) && isset($url['pass'])) $this->auth(str_replace('^', '@', $url['user']), $url['pass']);
	}

	// авторизация, обязательно после Hello
	public function auth($username = FW_MAILUSER, $password = FW_MAILPASS) {
		$this->checkConnect();
		// Start authentication
		$this->put("AUTH LOGIN", 334);
		$this->put(base64_encode($username), 334);
		$this->put(base64_encode($password), 235);
	}


	public function send($letter) {
		$this->checkConnect();
		$bad_rcpt = array();

		$this->mailFrom($letter->sender ? $letter->sender : $letter->from->email);
		$reps = $letter->allRecipients;
		
		// Attempt to send attach all recipients
		foreach($reps->items as $to) {
			try {
				$this->Recipient($to->email);
			} catch (ESMTP $e) {
				$bad_rcpt[] = $to->email;
			}
		}
		if (count($bad_rcpt)) { 
			$badaddresses = implode(', ', $bad_rcpt);
			throw new ESMTP("Bad recipients: ".$badaddresses);
		}
		$this->data((string)$letter);
	}

	// Закрытите содединения, желательно перед Close послать Quit
	public function close() {
		$this->helo_rply = null;
		if(!empty($this->handle)) {
			if ($this->connected) {
				$this->put("QUIT", 221);
			}
			// close the connection and cleanup
			fclose($this->handle);
			$this->handle = 0;
		}
	}

	// TLS
	// CODE 220 Ready to start TLS
	private function startTLS() {
		$this->put("STARTTLS", 220);

		// Begin encrypted connection
		if(!stream_socket_enable_crypto($this->handle, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) 
			throw new ESMTP("Can not open TLS");
	}

	// Отправака сообзения
	// Implements rfc 821: DATA <CRLF>
	// [data]<CRLF>.<CRLF>
	private function data($msg_data) {
		$this->checkConnect();

		$this->put("DATA", 354);
	
		// по стандарту не желательно отправлять строки длиной более 1000 символов
		// если новая строчка начинаестя с точки (символ конца данных), точку дублируем
		$lines = explode("\n", str_replace(array("\r\n", "\r"), "\n", $msg_data));

		// определяем есть ли заголвки
		$sp = strpos($lines[0], " "); $dv = strpos($lines[0],":");
		$in_headers = $dv && ($sp==false || $dv < $sp); 

		$max_line_length = 800; // used below; set here for ease in change

		while(list(,$line) = @each($lines)) {
			if($in_headers) $in_headers = $line != "";
			
			$lines_out = explode("\n", wordwrap($line, $max_line_length, "\n", true));
			// send the lines to the server
			while(list($no,$line_out) = @each($lines_out)) {
				if($in_headers && $no) 
					$line_out = "\t" . $line_out;
				elseif(strlen($line_out) > 0 && $line_out[0] == ".")
					$line_out = "." . $line_out;
				$this->put($line_out, 0, true);
			}
		}

		// message data has been sent
		$this->put(PHP_EOL.".", 250);
	}

	// HELO <SP> <domain> <CRLF>
	private function Hello() {
		try {
			$rply = $this->put("EHLO $this->Helo", 250);
		} catch (ESMTP $e) {
			$rply = $this->put("HELO $this->Helo", 250);
		}
		$this->helo_rply = $rply;
	}

	// начало отправки письма 
	// MAIL <SP> FROM:<reverse-path> <CRLF>
	// SUCCESS: 250
	private function mailFrom($from) {
		//$this->put("MAIL FROM:<$from>". ($this->do_verp ? "XVERP" : ""), 250);
		$this->put("MAIL FROM:<$from>", 250);
	}

	// уазание адресата
	// RCPT <SP> TO:<forward-path> <CRLF>
	// SUCCESS: 250,251
	private function Recipient($to) {
		$this->checkConnect();
		$rply = $this->put("RCPT TO:<$to>");
		if($rply->code != 250 && $rply->code != 251) throw new ESMTP($rply->message, $rply->code);
	}

	// отмена отправки письма
	// RSET <CRLF>
	// SUCCESS: 250
	private function Reset() {
		$this->checkConnect();
		$this->put("RSET", 250);
	}

	// запуск отправки пиьсма, далее cmd DATA
	// SAML <SP> FROM:<reverse-path> <CRLF>
	// SUCCESS: 250
	private function SendAndMail($from) {
		$this->checkConnect();
		$this->put("SAML FROM:$from", 250);
	}

	//@TODO Implements from rfc 821: TURN <CRLF>
	// SUCCESS: 250
	private function Turn() {
		throw new EMail("This method, TURN, of the SMTP is not implemented");
	}

	// Чтение сокета SMTP, если 4 символ '-' читаем дальше, если ' ' - остановка
	private function recieve() {
		$data = "";
		while($str = @fgets($this->handle,515)) {
			$data .= $str;
			// if 4th character is a space, we are done reading, break the loop
			if(substr($str,3,1) == " ") break;
		}
		$this->log->write("\n<= $data");
		return new SMTPReplay($data);
	}

	private function put($str, $expectCode = 0, $continue = false) {
		$this->log->write("\n => $str\n");
		fputs($this->handle, $str.PHP_EOL);
		if(!$continue) {
			$r = $this->recieve();
			if ($expectCode && $r->code != $expectCode)
				throw new ESMTP($r->message, $r->code);
			return $r;
		}
	}
}
?>