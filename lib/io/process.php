<?php
namespace FW\IO;

class Process extends \FW\Object {

	const IN_PIPE = 0;
	const OUT_PIPE = 1;
	const ERROR_PIPE = 2;

	private $handle;
	private $output;
	private $error;

	private $pipespec = array(
		Process::IN_PIPE => array("pipe", "r"),  // stdin is a pipe that the child will read from
		Process::OUT_PIPE => array("pipe", "w"),  // stdout is a pipe that the child will write to
		Process::ERROR_PIPE => array("pipe", "w") // stderr is a file to write to
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

		if (!is_resource($this->handle))
			throw new \Exception("Cannot call $command\n");

		//stream_set_blocking($this->pipes[Process::IN_PIPE], 0);
		//stream_set_blocking($this->pipes[Process::OUT_PIPE], 0);
		//stream_set_blocking($this->pipes[Process::ERROR_PIPE], 0);
		//stream_select 
		//$this->waitforstart();
	}

	function getError() { return $this->error; }
	function getOutput() { return $this->output; }

	function close() {
		fclose($this->pipes[Process::IN_PIPE]);
		fclose($this->pipes[Process::OUT_PIPE]);
		fclose($this->pipes[Process::ERROR_PIPE]);
		return proc_close($this->handle);
	}

	function waitforstop() {
		while (1) {
			$status = \proc_get_status($this->handle);
			if ($status['running']) usleep(100);
			else break;
		}
	}

	function waitforstart() {
		while (1) {
			$status = \proc_get_status($this->handle);
			if (!$status['running']) usleep(100);
			else break;
		}
	}

	function read() {
		if (feof($this->pipes[Process::OUT_PIPE])) return false;
		return stream_get_contents($this->pipes[Process::OUT_PIPE]);
	}

	function error() {
		if (feof($this->pipes[Process::ERROR_PIPE])) return false;
		return stream_get_contents($this->pipes[Process::ERROR_PIPE]);
	}

	function write($data) {
		fwrite($this->pipes[Process::IN_PIPE], $data);
		fflush($this->pipes[Process::IN_PIPE]);
	}

	function terminate($code = 15) {
		proc_terminate ($this->handle);
	}

	function run($command = '') {
		$this->open($command);
		$this->output = $this->read();
		$this->error = $this->error();
		$code = (int)$this->close();
		if ($code == -1 || $code == 255) $code = 0; // It's a hack! @todo remove
		return $code;
	}
}