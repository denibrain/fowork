<?php

$socket = socket_create (AF_INET, SOCK_STREAM, SOL_TCP);
socket_bind($socket, '127.0.0.1', 8029);
socket_listen($socket);

$a = socket_accept($socket);
echo socket_read($a, 8192);
socket_write($a, 'MALADEC');
//socket_close($a);


socket_close($socket);
?>