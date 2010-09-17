<?php

define('FW_CHARSET', 'utf-8');

require "../lib/basic.php";
require "../lib/shortcuts.php";
require "../lib/text/text.php";
require "../lib/net/header.php";
require "../lib/net/http.php";

//$http = new FW\Net\HTTP('http://hosttown.ru.local:80/demo.php');

$http = new FW\Net\HTTP('uaptest.ripn.net:8029');

$http->connect();
$http->send("", "POST");
$http->disconnect();

?>