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

dmn_sys_init(); // сердце демона!

$g_ppid = posix_getppid(); // pid родителя
$g_pid = posix_getpid(); // pid дочернего (текущего) процесса

// поехали!
while(1){
  // делаем что-то полезное.

  if($g_signals['SIGTERM'] == 1) // если получен сигнал завершения процесса, то не спеша, не паникуя тихонечко заканчиваем работу и выходим.
    break;

  if($sleep_time > 0)
    sleep(1); // спать одну секунду (на самом деле это идеологически не правильно, но для шаблона подойдет)
}


	}

	function init() {
		// 1. создаем дочерний процесс. при одном работающем
		// это может быть не нужно, но если планируется запуск одного
		// контрольного и нескольких рабочих процессов, то этот код необходим.

		if(-1 == ($this->parentPID = pcntl_fork()))
			exit("Could not fork.\n");

	  // очень важный момент! Здесь определяем в каком мы процессе после разветвления.
	  if($this->parentPID != 0){
		// 2. мы в родительском процессе
		// тут нам нужно отслеживать завершение дочерних процессов.
		// делается это банально просто: при создании процесса накручиваем счетчик дочерних процессов
		// при завершении дочернего процесса уменьшаем счетчик. Когда дочерних не осталось - выходим.
		// ждать завершения можно командой pcntl_wait.
		// написание этого кода я оставлю читателю в качестве домашного задания

		// для тех, кому лень думать есть приятные новости - дочерние процессы можно не ждать,
		// а делать в лучших традициях мира животных - выкидывать их из гнезда и тихонько помирать.
		exit("The end of parent process.\n");
	  }

	  // 3. мы в дочернем процессе!
	  // отцепляем процесс от консоли делаем его основным
	  if(-1 == posix_setsid())
		exit("Could not detach from terminal.\n");

  // устанавливаем хуки для обрабатываемых сигналов
  if(!pcntl_signal(SIGTERM, "dmn_sys_sig_handler"))
  exit("Could not setup SIGTERM handler.\n");

  if(!pcntl_signal(SIGHUP, "dmn_sys_sig_handler"))
  exit("Could not setup SIGHUP handler.\n");
}

	}


/ функция-хук обработки сигналов от операционной системы
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
 *  RC скрпт
 *  --------

#!/sbin/runscript

depend() {
  # выставляем зависимости
  need net
}

start() {
  # показываем юзеру что мы делаем
  ebegin "Starting my PHP daemon"

  # ведем лог
  echo --------------------- >> /var/log/my-php-daemon/rc.log
  date >> /var/log/my-php-daemon/rc.log

  # запускаем демоненка
  php-cgi -f /usr/lib/my-php-daemon/index.php >> /var/log/my-php-daemon/rc.log

  # конец
  eend $?
}

stop() {
  # показываем юзеру что мы делаем
  ebegin "Stopping my php daemon"
  # останавливаем демона так - ищем процесс, выполняющий файл /usr/lib/my-php-daemon/index.php и посылаем ему сигнал SIGTERM.
  # внимание! если таких процессов несколько, то все они получат сигналы SIGTERM.
  pkill -TERM -f /usr/lib/my-php-daemon/index.php

  # ведем лог
  date >> /var/log/my-php-daemon/rc.log
  echo --------------------- >> /var/log/my-php-daemon/rc.log

  # конец
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
declare(ticks=1);Дочерние процессы



На данный момент наш демон однопроцессовый. По ряду очевидных причин этого может быть недостаточно. Рассмотрим создание дочерних процессов.

$child_processes = array();

while (!$stop_server) {
    if (!$stop_server and (count($child_processes) < MAX_CHILD_PROCESSES)) {
        //TODO: получаем задачу
        //плодим дочерний процесс
        $pid = pcntl_fork();
        if ($pid == -1) {
            //TODO: ошибка - не смогли создать процесс
        } elseif ($pid) {
            //процесс создан
            $child_processes[$pid] = true;
        } else {
            $pid = getmypid();
            //TODO: дочерний процесс - тут рабочая нагрузка
            exit;
        }
    } else {
        //чтоб не гонять цикл вхолостую
        sleep(SOME_DELAY);
    }
    //проверяем, умер ли один из детей
    while ($signaled_pid = pcntl_waitpid(-1, $status, WNOHANG)) {
        if ($signaled_pid == -1) {
            //детей не осталось
            $child_processes = array();
            break;
        } else {
            unset($child_processes[$signaled_pid]);
        }
    }
}

Обработка сигналов



Следующая по важности задача — обеспечение обработки сигналов. Сейчас наш демон ничего не знает о внешнем мире, и убить его можно только завершением процесса через kill -SIGKILL. Это плохо. Это очень плохо — SIGKILL прервет процессы на середине. Кроме того, ему никак нельзя передать информацию.

Есть куча интересных сигналов, которые можно обрабатывать, но мы остановимся на SIGTERM — сигнале корретного завершения работы.

//Без этой директивы PHP не будет перехватывать сигналы
declare(ticks=1);

//Обработчик
function sigHandler($signo) {
    global $stop_server;
    switch($signo) {
        case SIGTERM: {
            $stop_server = true;
            break;
        }
        default: {
            //все остальные сигналы
        }
    }
}
//регистрируем обработчик
pcntl_signal(SIGTERM, "sig_handler");


Вот и все. Мы перехватываем сигнал — ставим флаг в скрипте — используем этот флаг, чтоб не запускать новые потоки и завершить основной цикл.

Поддержание уникальности демона


И последний штрих. Нужно, чтобы демон не запускался два раза. Обычно для этих целей используются т.н. .pid-файлы — файл, в котором записан pid данного конкретного демона, если он запущен.

function isDaemonActive($pid_file) {
    if( is_file($pid_file) ) {
        $pid = file_get_contents($pid_file);
        //проверяем на наличие процесса
        if(posix_kill($pid,0)) {
            //демон уже запущен
            return true;
        } else {
            //pid-файл есть, но процесса нет
            if(!unlink($pid_file)) {
                //не могу уничтожить pid-файл. ошибка
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


А после демонизации — нужно записать в pid-файл текущий PID демона.

file_put_contents('/tmp/my_pid_file.pid', getmypid());



ИП Ефремов
Знание
Гизатуллин В.В.
Хафизов
ИП Забаров (Симай)
Мальджини
ООО IRA
УГСЗН РБ
Новиков, ИП
ООО Бэствэб
«Инфокоммуникации»
ООО «Фотоника Плюс»
ООО «ЮнитВеб»
ООО «ЭВМ ПЛЮС»
ООО Деловой город»
УК Курсор
ООО «М2М Баштелематика»
ООО «ВебСервисГлобал»
ЧЛ Дымов
ООО ТД «Клапан»
Кашлев
ООО «Роспроектизыскания»
Управление контро. при МинОбр РБ
Давлетшин И.И.
ООО «Барвик»
ИП Зорин А.В.
ООО «Стандарт»
ИТС