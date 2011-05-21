<?php
declare(ticks=1);

namespace FW\App;
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of demon
 *
 * @author denis
 */
class Demon extends ConsoleApp {

	private $parentPID
	private $childPID;

	function run() {


$g_signals = array(
  'SIGTERM' => 0,
  'SIGHUP' => 0,
);

$g_ppid = null;
$g_pid = null;

dmn_sys_init(); // ������ ������!

$g_ppid = posix_getppid(); // pid ��������
$g_pid = posix_getpid(); // pid ��������� (��������) ��������

// �������!
while(1){
  // ������ ���-�� ��������.

  if($g_signals['SIGTERM'] == 1) // ���� ������� ������ ���������� ��������, �� �� �����, �� ������� ��������� ����������� ������ � �������.
    break;

  if($sleep_time > 0)
    sleep(1); // ����� ���� ������� (�� ����� ���� ��� ������������� �� ���������, �� ��� ������� ��������)
}


	}

	function init() {
		// 1. ������� �������� �������. ��� ����� ����������
		// ��� ����� ���� �� �����, �� ���� ����������� ������ ������
		// ������������ � ���������� ������� ���������, �� ���� ��� ���������.

		if(-1 == ($this->parentPID = pcntl_fork()))
			exit("Could not fork.\n");

	  // ����� ������ ������! ����� ���������� � ����� �� �������� ����� ������������.
	  if($this->parentPID != 0){
		// 2. �� � ������������ ��������
		// ��� ��� ����� ����������� ���������� �������� ���������.
		// �������� ��� �������� ������: ��� �������� �������� ����������� ������� �������� ���������
		// ��� ���������� ��������� �������� ��������� �������. ����� �������� �� �������� - �������.
		// ����� ���������� ����� �������� pcntl_wait.
		// ��������� ����� ���� � ������� �������� � �������� ��������� �������

		// ��� ���, ���� ���� ������ ���� �������� ������� - �������� �������� ����� �� �����,
		// � ������ � ������ ��������� ���� �������� - ���������� �� �� ������ � �������� ��������.
		exit("The end of parent process.\n");
	  }

	  // 3. �� � �������� ��������!
	  // ��������� ������� �� ������� ������ ��� ��������
	  if(-1 == posix_setsid())
		exit("Could not detach from terminal.\n");

  // ������������� ���� ��� �������������� ��������
  if(!pcntl_signal(SIGTERM, "dmn_sys_sig_handler"))
  exit("Could not setup SIGTERM handler.\n");

  if(!pcntl_signal(SIGHUP, "dmn_sys_sig_handler"))
  exit("Could not setup SIGHUP handler.\n");
}

	}


/ �������-��� ��������� �������� �� ������������ �������
function dmn_sys_sig_handler($signo)
{
  global $g_signals;

  switch ($signo){
  case SIGTERM:
    $g_signals['SIGTERM'] = 1;
    break;
  case SIGHUP:
    $g_signals['SIGHUP'] = 1;
    break;
  default:
    $g_signals['OTHER'] = $signo;
  }
}

}

/**
 *  RC �����
 *  --------

#!/sbin/runscript

depend() {
  # ���������� �����������
  need net
}

start() {
  # ���������� ����� ��� �� ������
  ebegin "Starting my PHP daemon"

  # ����� ���
  echo --------------------- >> /var/log/my-php-daemon/rc.log
  date >> /var/log/my-php-daemon/rc.log

  # ��������� ���������
  php-cgi -f /usr/lib/my-php-daemon/index.php >> /var/log/my-php-daemon/rc.log

  # �����
  eend $?
}

stop() {
  # ���������� ����� ��� �� ������
  ebegin "Stopping my php daemon"
  # ������������� ������ ��� - ���� �������, ����������� ���� /usr/lib/my-php-daemon/index.php � �������� ��� ������ SIGTERM.
  # ��������! ���� ����� ��������� ���������, �� ��� ��� ������� ������� SIGTERM.
  pkill -TERM -f /usr/lib/my-php-daemon/index.php

  # ����� ���
  date >> /var/log/my-php-daemon/rc.log
  echo --------------------- >> /var/log/my-php-daemon/rc.log

  # �����
  eend $?
}
 */

/**
 * Checkalka
 */

$g_vs_gw = "http://gw1.viasms.ru?"
$g_vs_eid = "12345";
$g_vs_password = "secure_word";

$g_needed = array(
  'my-php-daemon' => array(
    'regexp'=>'/php-cgi.+my-php-daemon/',
    'reset_cmd'=>'/etc/init.d/my-php-daemon zap --nocolor',
    'start_cmd'=>'/etc/init.d/my-php-daemon start --nocolor'
  ),
);

// fill array with system fields
$needed = array();

foreach($g_needed as $key=>$val){
  $needed[$key] = $val;
  $needed[$key]['running'] = 0;
}

$exec = shell_exec("ps -C php-cgi -o pid= -o command=");
$pss = explode("\n", $exec);

foreach($needed as $key=>$val)
  if(!$val['running'])
    foreach($pss as $ps)
      if(preg_match($val['regexp'], $ps))
        $needed[$key]['running'] = 1;

foreach($needed as $key=>$val){
  if(!$val['running']){
    print date("Ymd-His") . "\n";

    print shell_exec($val['reset_cmd']);
    $cmdresult = shell_exec($val['start_cmd']);
    print $cmdresult;

    $msg = $key . ' service is down. ' . $cmdresult;
    $pass = md5(md5($g_vs_password) . $msg);

    $url = $g_vs_gw . '?' .
      'eid=' . urlencode($g_vs_eid) .
      '&pass=' . urlencode($pass) .
      '&msg=' . urlencode($msg);

    print $url . "\n";
    readfile($url);
    print "\n";
  }
}
?>
declare(ticks=1);�������� ��������



�� ������ ������ ��� ����� ���������������. �� ���� ��������� ������ ����� ����� ���� ������������. ���������� �������� �������� ���������.

$child_processes = array();

while (!$stop_server) {
    if (!$stop_server and (count($child_processes) < MAX_CHILD_PROCESSES)) {
        //TODO: �������� ������
        //������ �������� �������
        $pid = pcntl_fork();
        if ($pid == -1) {
            //TODO: ������ - �� ������ ������� �������
        } elseif ($pid) {
            //������� ������
            $child_processes[$pid] = true;
        } else {
            $pid = getmypid();
            //TODO: �������� ������� - ��� ������� ��������
            exit;
        }
    } else {
        //���� �� ������ ���� ���������
        sleep(SOME_DELAY);
    }
    //���������, ���� �� ���� �� �����
    while ($signaled_pid = pcntl_waitpid(-1, $status, WNOHANG)) {
        if ($signaled_pid == -1) {
            //����� �� ��������
            $child_processes = array();
            break;
        } else {
            unset($child_processes[$signaled_pid]);
        }
    }
}

��������� ��������



��������� �� �������� ������ � ����������� ��������� ��������. ������ ��� ����� ������ �� ����� � ������� ����, � ����� ��� ����� ������ ����������� �������� ����� kill -SIGKILL. ��� �����. ��� ����� ����� � SIGKILL ������� �������� �� ��������. ����� ����, ��� ����� ������ �������� ����������.

���� ���� ���������� ��������, ������� ����� ������������, �� �� ����������� �� SIGTERM � ������� ���������� ���������� ������.

//��� ���� ��������� PHP �� ����� ������������� �������
declare(ticks=1);

//����������
function sigHandler($signo) {
    global $stop_server;
    switch($signo) {
        case SIGTERM: {
            $stop_server = true;
            break;
        }
        default: {
            //��� ��������� �������
        }
    }
}
//������������ ����������
pcntl_signal(SIGTERM, "sig_handler");


��� � ���. �� ������������� ������ � ������ ���� � ������� � ���������� ���� ����, ���� �� ��������� ����� ������ � ��������� �������� ����.

����������� ������������ ������


� ��������� �����. �����, ����� ����� �� ���������� ��� ����. ������ ��� ���� ����� ������������ �.�. .pid-����� � ����, � ������� ������� pid ������� ����������� ������, ���� �� �������.

function isDaemonActive($pid_file) {
    if( is_file($pid_file) ) {
        $pid = file_get_contents($pid_file);
        //��������� �� ������� ��������
        if(posix_kill($pid,0)) {
            //����� ��� �������
            return true;
        } else {
            //pid-���� ����, �� �������� ���
            if(!unlink($pid_file)) {
                //�� ���� ���������� pid-����. ������
                exit(-1);
            }
        }
    }
    return false;
}

if (isDaemonActive('/tmp/my_pid_file.pid')) {
    echo 'Daemon already active';
    exit;
}


� ����� ����������� � ����� �������� � pid-���� ������� PID ������.

file_put_contents('/tmp/my_pid_file.pid', getmypid());



�� �������
������
���������� �.�.
�������
�� ������� (�����)
���������
��� IRA
����� ��
�������, ��
��� �������
�����������������
��� ��������� ����
��� ��������
��� ���� ���ѻ
��� ������� �����
�� ������
��� ��2� �������������
��� ����������������
�� �����
��� �� �������
������
��� ��������������������
���������� ������. ��� ������ ��
��������� �.�.
��� �������
�� ����� �.�.
��� ���������
���