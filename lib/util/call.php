<?php
namespace FW;

class ERemoteCall extends EApp {
	function __construct($cmd, $answer, $resultCode) {
		parent::__construct("[$cmd]\n$answer", $resultCode);
	}
}

function remotecall($server, $cmd) {
	$descriptorspec = array(
		0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
		1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
		2 => array("file", "./error-output.txt", "a") // stderr is a file to write to
	);

	$cwd = ".";
	$env = array();

	$cmd = sprintf(CMD, SHELL, $server, $cmd);

	$process = proc_open(
		$cmd,
		$descriptorspec, $pipes, NULL, NULL
	);
	
	$result = NULL;

	if (is_resource($process)) {
		fclose($pipes[0]);
		
		$answer = stream_get_contents($pipes[1]);
	    fclose($pipes[1]);

		$result = proc_close($process);
		if ($result)
			throw new ERemoteCall($cmd, $answer, $result);
	}
	
}



function call($command, &$out, $in = array()) {
    $pipespec = array(
	    0 => array("pipe", "r"),  // stdin is a pipe that the child will read from
	    1 => array("pipe", "w"),  // stdout is a pipe that the child will write to
	    2 => array("pipe", "w") // stderr is a file to write to
    );
                        
    $cwd = '/tmp';
    $env = array();
                                    
    $process = proc_open($command, $pipespec, $pipes, $cwd, $env);
                                            
     if (is_resource($process)) {
    	foreach($in as $str) {
    	    sleep(3);
    	    fwrite($pipes[0], $str);
    	}
        fclose($pipes[0]);

	if (!feof($pipes[1]))
            $out[0] = fgets($pipes[1], 1024);
        fclose($pipes[1]);
        if (!feof($pipes[2]))
            $out[1] = fgets($pipes[2], 1024);
        fclose($pipes[2]);
        $code = proc_close($process);
    } else
	 throw new Exception("Cannot run program $command");
                                                                            
    return $code;
}

?>