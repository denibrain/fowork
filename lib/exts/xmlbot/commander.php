<?php
namespace FW\Exts\XMLBot;

use \FW\Net\Sockets\Message;

class Commander extends \FW\Object {

	private $username;
	private $password;
	private $client;
	private $connected = false;

	function setUserName($userName) { $this->userName = $username; }
	function getUserName() { return $this->username; }
	function setPassword($password) { $this->password = $password; }
	function getPassword() { return $this->password; }
	
	function __construct($host, $port, $username, $password) {
		$this->client = new \FW\Net\Sockets\Client();
		$this->client->host = $host;
		$this->client->port = $port;
		$this->username = $username;
		$this->password = $password;
	}
		
	public function connect() {
		$this->client->connect();
		                   
		$m = new Message('LOGIN');
		$m->body->add(E('login', A('name', $this->userName, 'password', $this->password)));
		$m = $this->doRequest($m);

		if ($m->type != 'OK') {
			$this->client->disconnect();
			print_r($m->type);
			throw new \Exception ('User or password is wrong');
		}

		$this->connected = true;
	}
	
	public function command($action, $target, \FW\Text\Element $params) {
		if (!$this->connected) return;
		$request = new Message('COMMAND');
		$command = $request->body->add(E('command',
				array('module'=>$target, 'name'=>$action, 'id'=>(string)1), $params));
		$response = $this->doRequest($request);
		if ($response->type != 'OK') {
			throw new ECommand('Syntax request fail: '.$response->description, $response->code);
		}
		$command = $response->body->command[0];
		if ($command->status != 'ok') {
			throw new ECommand('Syntax execute of commnand fail: '.$command->description, $command->code);
		}
		switch ($command->result[0]->type) {
			case 'void': return;
			case 'bool': return $command->result[0]->value === 'true';
			case 'string': return $command->result[0]->value;
		};
		return $command->result;
	}
	
	public function disconnect() {
		if (!$this->connected) return;
		$m = new Message('QUIT');
		$m->body->add(E('quit'));
		
		$r = $this->doRequest($m);
		$this->client->disconnect();
		$this->connected = false;
	}
	
	private function doRequest (Message $request) {
		$this->client->write((string) $request);
		$r = new Message();
		$r->parse($this->client->read());
		return $r;
	}
}

class ECommand extends \Exception {}