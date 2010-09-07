<?php
namespace FW\Util;

class Call {

	const IN_PIPE = 0;
	const OUT_PIPE = 1;
	const ERROR_PIPE = 2;

	private $handle;

	private $pipespec = array(
		Call::IN_PIPE => array("pipe", "r"),  // stdin is a pipe that the child will read from
		Call::OUT_PIPE => array("pipe", "w"),  // stdout is a pipe that the child will write to
		Call::ERROR_PIPE => array("pipe", "w") // stderr is a file to write to
		//			2 => array("file", "./error-output.txt", "a") // stderr is a file to write to

	);

	private $pipes = array();
	private $cwd;
	private $env;
	private $command;

	function __construct($defCommand = 'ls') {
		$this->cwd = NULL;
		$this->env = NULL;
		$this->command = $defCommand;

	}

	function open($command = '') {
		$proc_options = array();//array('bypass_shell' => "1");

		$this->handle = proc_open($command ? $command : $this->command,
			$this->pipespec, $this->pipes, $this->cwd, $this->env, $proc_options);
		var_dump($this->handle);

		if (!is_resource($this->handle))
			throw new \Exception("Cannot call $command\n");
		$this->waitforstart();
	}

	function close() {
		fclose($this->pipes[Call::IN_PIPE]);
		fclose($this->pipes[Call::OUT_PIPE]);
		fclose($this->pipes[Call::ERROR_PIPE]);
		return proc_close($this->handle);
	}

	function waitforstop() {
		echo 'wait to stop';
		while (1) {
			$status = \proc_get_status($this->handle);
			if ($status['running']) usleep(100);
			else break;
		}
		echo ".\n";
	}

	function waitforstart() {
		echo 'wait to start';
		while (1) {
			$status = \proc_get_status($this->handle);
			if (!$status['running']) usleep(100);
			else break;
		}
		echo ".\n";
	}

	function read() {
		if (feof($this->pipes[Call::OUT_PIPE])) return false;
		return stream_get_contents($this->pipes[Call::OUT_PIPE]);
	}

	function error() {
		if (feof($this->pipes[Call::ERROR_PIPE])) return false;
		return stream_get_contents($this->pipes[Call::ERROR_PIPE]);
	}

	function write($data) {
		fwrite($this->pipes[Call::IN_PIPE], $data);
		fflush($this->pipes[Call::IN_PIPE]);
	}

	function terminate($code = 15) {
		proc_terminate ($this->handle);
	}

	static function exec($command) {
		$call = new Call($command);
		$call->open();
		$call->waitforstop();
		$data = $call->read();
		return array($call->close(), $data);
	}
}