<?php
include 'Netcontrol4r4s1a.inc.php';

$controller = new Netcontrol4r4s1a();

 $controller->init('192.168.1.100');
//$controller->init('192.168.100.80');

echo $controller->showConfig();
