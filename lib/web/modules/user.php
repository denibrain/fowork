<?php
use FW\VCL\Forms;

class EInvalidSession extends \Exception {function __construct() {parent::__construct('Неверная сессия');}}
class ESessionExpired extends \Exception {function __construct() {parent::__construct('Сессия закончилась');}}
class ESessionStopped extends \Exception {function __construct() {parent::__construct('Сессия остановлне администратором');}}


class User extends \FW\Web\Module {

	private $id = 0;
	private $type = 'guest';
	private $groups = array();
	private $name = 'Guest';
	private $SUID = 0;
	
	private $session;
	
	function __construct($app) {
		parent::__construct($app);
		if (isset($_COOKIE['SUID']) &&
			$session = $this->dsSession(A('id', (int)$_COOKIE['SUID']))->getA())
		{
			if (!$session) throw new EInvalidSession();

			$this->SUID = (int)$_COOKIE['SUID'];
			if ($session['ip']!=$_SERVER['REMOTE_ADDR']) throw new EInvalidSession();
			if ($session['diff'] > 3600) throw new ESessionExpired();
			if (!$session['active']) throw new ESessionStopped();
			
			$this->id = $session['userid'];

			$this->type = $session['type'];
			$this->name = $session['name'];
			$this->loadGroups();
			$this->dpProlongate($this->SUID);
			setcookie('SUID', $this->SUID, time() + 3600);
		}
		else {
			$this->id = 0;
			$this->type = 'guest';
			$this->name = 'Гость';
			$this->groups = array('guest');
			if (isset($_COOKIE['SUID']))
				setcookie('SUID', $_COOKIE['SUID'], time() - 3600);
		}
	}

	function __get($key) {
		switch($key) {
			// TODO groups must be removed from access
			case 'groups': return $this->groups;
			case 'name': return $this->name;
			case 'type': return $this->type;
			case 'id': return $this->id;
			default: return parent::__get($key);
		}
	}

	function loadGroups() {
		$this->groups = array_merge($this->dsGroups(A('type', $this->type))->lst(), array('users'));
	}

	function checkPermission($group) {
		return in_array($group, $this->groups);
	}

	function startSession() {
		$this->SUID = $this->dpStartSession($this->id);
		setcookie('SUID', $this->SUID, time() + 3600);
	}
	
	function stopSession() {
		$this->dpStopSession($this->SUID);
		setcookie('SUID', $this->id, time() -10);
		$this->id = 0;
	}

	function passhash($pass) {return sha1('_6u'.$pass);}
	
	function permpwd($pass, $type) {
		return false!=$this->dsLogin(A('type', $type,
			'login', $this->id, 'pass', $this->passhash($pass)))->get();
	}
	
	function create($name, $pass, $email, $type) {
		return $this->dpCreate($name, $this->passhash($pass), $email, $type);
	}
	
	function login($params) {
		$params['pass'] = $this->passhash($params['pass']);
		if (!($userinfo = $this->dsLogin($params)->get()))
			throw new EFormUser('С указаными данными пользователь не найден');

		if (!array_pop($userinfo))
			throw new EFormUser('Логин заблокирован');

		list($this->id, $this->name, $this->type) = $userinfo;

		$this->loadGroups();
		$this->startSession();
	}

	function logout() {
		if ($this->SUID)
			$this->stopSession();
	}

	function fogot($email) {
		$ds = $this->dsByemail(A('email', $email));
		if (!$ds->count()) throw new Exception('Данный пользователь в системе не найден');
		while ($data = $ds->getA()) {
			$data['pass'] = (string)new FW\Util\Password();
			$data['login'] = $data['id'];
			$this->dpChangePassword($data['id'], $this->passhash($data['pass']));
			$this->app->mailTo(array(E('fogot', $data), 2=>'user.Fogot.mail'), 'Востановление пароля', $data['email']);
		}
	}

	function newMessage($message) {
		$message = q($message);
		$this->execSQL("INSERT INTO #messages (name, createdate, owner_id) VALUES ('$message', NOW(), {$this->id})");
	}	
	
	/* Visual Section ------------------ */
	
	function displayUserInformer() {
		if (!$this->id) return E('loginbox');
		return E('logined', A('type', $this->type, 'name', $this->name));
	}
	
	function displayLoginBox() {
		return E('loginbox');
	}

	/* show user messages
	  @param user: user:id
	*/
	function displayInbox($params) {
		return $this->dsInbox($params)->items(E('messages'), 'message');
	}
}