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

		if (isset($_COOKIE['SUID']) and
			$session = $this->dsSession(A('id', (int)$_COOKIE['SUID']))->getA()) {
			
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
	
	function onLogin($form) {
		$values = $form->getValues();
		$params['login'] = (int)substr($values['uid'], 1);
		$params['type'] = substr($values['uid'], 0, 1);
		$params['pass'] = $values['pass'];
		$this->login($params);
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

	function fogot($form) {
		$ds = $this->dsByemail($form->getValues());
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
	
	function displayLogout() {
		$this->logout();
		throw new ERedirect('login');
		return E('quit');
	}

	function displayLogin() {
		if($this->id)
			throw new ERedirect(substr($r = $_SERVER['REQUEST_URI'], 0, strpos($r, '/') + 1));
		
		$form = $this->form('login');
	
		if ($form->proceed() == $form::OK) 
			throw new ERedirect($_SERVER['REQUEST_URI']);
		else
			return E('login', $form->display());
	}

	function displayFogot() {
		$form = $this->form('fogot');
		if ($form->proceed() == $form::OK) 
			return E('sended');
		else
			return E('fogot', $form->display());
	}
	
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
	
	/* display personal info
	@param id client ID
	*/
	function displayChangePass($params) {
		if (!($user = $this->dsUser($params)->getA()))
			throw new E404();
		
		$form = new Forms\Form('changepass');
		$form->add(new Forms\Button("изменить", "change"));
		
		if ($form->proceed() == $form::OK) {
			$user['pass'] = (string)new \FW\Util\Password();
			$user['login'] = $user['id'];
			$pass =  $this->passhash($user['pass']);
			$this->dpChangepassword($user['id'], $pass);
			$this->app->mailTo(
				array(E('changedpass', $user), 2=>'user.ChangePass.mail'), 'Смена пароля', $user['email']);
			
			return E('complete');
		}
		else return E('welcome', $form->display());
	}	
}