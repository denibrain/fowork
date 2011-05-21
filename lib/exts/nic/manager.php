<?php
namespace FW\Exts\Nic;
use Objects\Contract;
use Objects\Domain;

/**
 * Description of nicmanager
 *
 * @author a.garipov
 */

class Manager {
	private $client;
	private $login;
	private $password;

	function __construct($login, $password) {
		$this->login = $login;
		$this->password = $password;
		$this->client = new Client;
	}

	function createRequest($name, $operation, $nicd = false) {
		$request = new Request;
		$request->headers->setArray(array(
			'login' => $this->login,
			'password' => $this->password,
			'lang' => 'ru',
			'request' => $name,
			'operation' => $operation,
			'request-id' => $this->getRequestId()
		));
		$request->headers['subject-contract'] = $nicd;
				
		return $request;
	}

	function send($request) {
		$response = $this->client->doRequest($request);
		if($response->headers['State'] != '200 OK')
			throw new ENic($response);
		return $response;
	}

	/**
	 * —оздает контракт и возвращает идентификатор созданного контракта.
	 * ѕример: 929785/NIC-D.
	 * @param Contract $data
	 * @return string
	 */
	function contractCreate(Contract $contr) {
		$request = $this->createRequest('contract', 'create');

		$g = $request->addGroup('contract');
		$g->setArray($contr->getAsMagicArray());

		$this->send($request)->params['login'];
	}

	/**
	 * ¬озвращает объект контракта ContractIP или ContractUR
	 * @param string $contractId номер контракта (напр. "927986/NIC-D")
	 * @return ContractIP|ContractUR
	 */
	function contractGet($contractId) {
		$groups = $this->send(
			$this->createRequest('contract', 'get', $contractId))
			->getGroupsByTitle("contract");
		
		if(isset($groups[0])) {
			$g = $groups[0];
			$contr = $g['contract-type'] == 'PRS' ? new ContractPRS : new ContractORG;
			$contr->loadFromMagicArray($g);
			return $contr;
		} 
		return null;
	}

	/**
	 * ѕоиск контактов по заданым параметрам
	 * —писок параметров:
	 *
	 * @param array $params
	 * ќбщие параметры: contract-num, e-mail, domain, is-resident.
	 * ƒл€ поиска по организаци€м: org, org-r, code.
	 * ƒл€ поиска по физическим лицам и »ѕ: person, person-r, passport.
	 * @param int $limit
	 *  оличество блоков с данными по анкетам клиентов, выдаваемых в ответе.
	 * ¬ случае, если вы печатаете результаты ответа на странице, то значение этого пол€ можно установить, например, равным 10.
	 * Ѕаза данных будет выдавать по 10 анкет.
	 * ѕоле может содержать число от 1 до 64000.
	 * Ќеоб€зательное поле (по умолчанию его значение равно 10).
	 * ќднострочное поле.
	 * @param int $offset
	 * ѕор€дковый номер блока с данными из анкет клиентов,
	 * начина€ с которого (из числа найденных в базе) блоки будут выдаватьс€ в ответе.
	 * ƒл€ постраничной выдачи услуг в вашем интерфейсе, если на каждой странице отображаетс€ по 10 анкет,
	 * дл€ первой страницы это поле нужно устанавливать равным 1, дл€ второй - 11, дл€ третей - 21, и так далее.
	 * ѕоле может содержать число от 1 до 64000.
	 * Ќеоб€зательное поле (по умолчанию его значение равно 1).
	 * ќднострочное поле.
	 * @return array массив с общим числом, удовлетвор€ющих условию, контрактов - в первом элементе
	 * и с массивом групп во втором
	 */
	function contractSearch($params, $limit = 10, $offset = 1) {
		$request = $this->createRequest('contract', 'search');
		
		$g = $request->addGroup('contract');
		$g->setArray($params);
		$g['contracts-limit'] = strval($limit);
		$g['contracts-first'] = strval($offset);

		$response = $this->send($request);
		$contractListGroup = $response->getGroupByTitle('contracts-list');
		$found = intval($contractListGroup['contracts-found']);
		$contractGroups = $response->getGroupsByTitle('contract');
		$namedContractGroups = array();
		foreach($contractGroups as $g){
			$namedContractGroups[$g['contract-num']] = $g;
		}

		return array($found, $namedContractGroups);
	}

	/**
	 * @param string $contractId номер контракта (напр. "927986/NIC-D")
	 * @return boolean
	 */
	function contractDelete($contractId) {
		$this->send($this->createRequest('contract', 'delete', $contractId));
		return true;
	}
	
	/**
	 * @param string $contractId номер контракта (напр. "927986/NIC-D")
	 * @param Contract $contr
	 * пол€: phone, faxNo, email, password, techPassword, pAddr, mntNfy, dAddr, addressR
	 * @return boolean
	 */	
	function contractUpdate($contractId, Contract $contr) {
		$g = $this->createRequest('contract', 'update', $contractId)->addGroup('contract');
		$g->setArray(D($contr->getAsMagicArray(),
			'phone,fax-no,e-mail,password,tech-password'.
			',p-addr,mnt-nfy,d-addr,address-r'));

		$this->send($request);
		return true;
	}

	function contactCreate($contractId, Contact $cont) {
		
	}

	function contactUpdate($contractId, Contact $cont) {

	}

	function contactSearch($contractId, Contact $cont) {

	}

	function contactDelete($contractId, Contact $cont) {

	}
	
	public function domainCreate($contractId, Domain $domain, $contactId = false)	{
		$request = $this->createRequest('order', 'create', $contractName);

		$g = $request->addGroup('order-item');

		$zone = strtolower(substr($domain->domain, strpos($domain->domain, '.')+1));

		if ($zone == 'ru' ||$zone == 'su') {
			$g['service'] = "domain_$zone";
			$g['template'] = 'client_ru';
			$g['type'] = 'CORPORATE';
		} else {
			if (!$contactId) {
				$rSearch = $this->createRequest('contact', 'search');
				$gContact = $rSearch->addGroup('contact');
				$gContact['contract'] = $contractId;
				$gContact['status'] = 'registrant';
				$gCList = $this->send($rSearch)->getGroupByTitle('contracts-list');
				if (!$gCList['contacts-found']) {
					$contact = new Objects\Contact();
					$contract = $this->contractGet($contractId);
					$contact->loadFormContract($contract);
					$contactId = $this->contactCreate($contractId, $contact);

					$rSearch = $this->createRequest('contact', 'search');
				}
			}
			$g['admin-c'] = $contactId;
			$g['bill-c'] = $contactId;
			$g['tech-c'] = $contactId;
			if ($zone == 'info' || $zone=='biz' || $zone == 'com' || $zone=='net') {
				$g['service'] = "domain_rpp";
				$g['template'] = 'client_rrp';
			} else {
				$g['service'] = "domain_epp_$zone";
				$g['template'] = 'client_rrp';
			}
		}

		$g['action'] = 'new';
		$g['period'] = 1;

		if ($zone == 'tel' && isset($domain->nserver)) $domain->nserver = NULL;

		$this->send($request);
		return true;
	}

	private function getRequestId()	{
		return date('Ymdhis').'.'.sprintf('%05d', rand(0, 9999)).'@hosttown.ru';
	}
}

class ENic extends Exception {
	public $errors = array();

	public function __construct(Response $response) {
		$message = $response->headers['State'];

		foreach($response->getGroupsByTitle('errors') as $group) 
			$message .= "\n".join("\n", $group->getArray());

		$code = substr($message, 0, 3);
		parent::__construct($message, $code);
	}
}