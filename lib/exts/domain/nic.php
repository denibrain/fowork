<?php
namespace FW;

// https://www.nic.ru/dns/dealer

class ENicRequest extends EApp {
	function __construct($message) {
		parent::__construct($message, 0);
	}
}

class Nic {
    public $request; // contract, order, service-object, service, contact, server
    public $operation; // search, get, create, update, delete, swap
    public $subjectContract = 0;
    
	private $head;
    private $ch;

    public function __construct($login, $password) {
        $this->head = "login:$login/NIC_REG/ADM\npassword:$password\nlang:ru\n";
        $this->ch = curl_init();
    }

    public function __destruct() {
        curl_close($this->ch);
    }

    public function send($data) {
		$rid = date("Ymdhis").".".sprintf("%05d", rand(0, 9999));
		$req  = $this->head . "request-id:$rid@hosttown.ru\n".
			"request:$this->request\n;operation:$this->operation\n";

		if ($this->subjectContract) $req .= "subject-contract:$this->subjectContract\n";
			
		foreach($contract as $field) {
			list($name, $value) = $field;
			if (!$name) {
				$req.= "\n[$value]\n";
			} else {
				$req .= "$name:$value\n";
			}
		}

		$data = array();
        $data['SimpleRequest'] = $req;
		
        curl_setopt($this->ch, CURLOPT_URL, 'https://www.nic.ru/dns/dealer');
        curl_setopt($this->ch, CURLOPT_POST, 1);
        curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, 'false');
        curl_setopt($this->ch, CURLOPT_SSL_VERIFYHOST, 'false');

        $f = fopen("$rid.txt", 'w');
        curl_setopt($this->ch, CURLOPT_FILE, $f);
        if (curl_exec($this->ch)==='false')
            throw new Exception(curl_error($this->ch));
		fclose($f);
		$mess = file_get_contents("$rid.txt");
			
		$status = curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
		if ($status==402) {
			throw new ENicRequest($mess);
		}
		if ($status == 200) {
			return $mess;
		}
    }

	/*
	 type 0 - פטח 1 - ‏נ  2- ָֿ
	*/
	function createContract($type, $info) {
		$this->request = 'contract';
		$this->operation = 'create';
		$this->subjectContract = 0;
		
		$types = array('PRS', 'ORG', 'PRS');
		$contract = array(
			array('', 'contract'),
			array('contract-type', $types[$type])
		);
		$contract[] = array('password', $info['pass']);
		$contract[] = array('tech-password', $info['tpass']);
		if ($type==1) {
			$contract[] = array('org', $info['whoisname']);
			$contract[] = array('org-r', $info['name']);
			$contract[] = array('kpp', $info['kpp']);
		} else {
			$contract[] = array('person', $info['whoisname']);
			$contract[] = array('person-r', $info['name']);
			$contract[] = array('passport', $info['passport']);
			$contract[] = array('birth-date', $info['birthday']);
		}
		$contract[] = array('country', 'RU');
		$contract[] = array('currency-id', 'RUR');
		$contract[] = array('p-addr', "{$info['postindex']}, {$info['postaddress']}");
		$contract[] = array('p-addr', $info['postrecipient']);
		if ($type) {
			$contract[] = array('d-addr', $info['factaddress']);
			$contract[] = array('code', $info['inn']);
			$contract[] = array('address-r', $info['orgaddress']);
		}

		foreach(explode(",", $info['phone']) as $p) $contract[] = array("phone", trim($p));
		foreach(explode(",", $info['fax']) as $p) $contract[] = array("fax-no", trim($p));
		foreach(explode(",", $info['e-mail']) as $p) $contract[] = array("e-mail", trim($p));
		$contract[] = array("mnt-nfy", SUPPORT_EMAIL);
		
		$data = $this->send($contract);
		if (!preg_match('login: ([0-9]+)/NIC-D', $data, $regs)) {
			throw new Exception('Not supported answer');
		}
		return $regs[1];
		
	}
	
	/* @todo UPdate Contract */
	function updateContract($id, $data) {
		$this->subjectContract = $id;
		/* ...*/
	}
	
	function CreateDomain($id, $domain) {
		list($name, $tld) = explode('.', $domain);
		$this->subjectContract = $id;
		
		if ($tld == 'ru' || $tld == 'su') {
			$data = array(
				array('', 'order-item'),
				array('service', "domain_$tld"), 
				array('template', "client_$tld"), 
				array('action', 'new'), 
				array('domain', $domain), 
				array('private-person', 'ON'),
				array('type', 'CORPORATE')
			);
			if ($ns)
				foreach($ns as $s) $data[]= array('nserver', $s);
		}

		$text = $this->send($contract);
		if (!preg_match('order_id:[0-9]+', $text, $regs)) {
			throw new Exception("Unsupported answer");
		}
		return $regs[1];
	}
	
	function changeDomainNs($id, $domain, $ns) {
		list($name, $tld) = explode('.', $domain);
		$this->subjectContract = $id;
		
		if ($tld == 'ru' || $tld == 'su') {
			$data = array(
				array('', 'order-item'),
				array('service', "domain_$tld"), 
				array('template', "client_$tld"), 
				array('action', 'update'), 
				array('domain', $domain), 
			);
			foreach($ns as $s) $data[]= array('nserver', $s);
		}		
		if (!preg_match('order_id:[0-9]+', $text, $regs)) {
			throw new Exception("Unsupported answer");
		}
		return $regs[1];
	}
}




?>