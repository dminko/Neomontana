<?php
include 'Netcontrol4r4s1a.inc.php';

$controller = new Netcontrol4r4s1a();

//$controller->init('192.168.1.100');
$controller->init('192.168.100.80');

$sens = $controller->readRegisters();
print_r($sens);


$controller->writeRegisters('Relay1', 0);
$controller->writeRegisters('Relay2', 0);
$controller->writeRegisters('Relay3', 0);
$controller->writeRegisters('Relay4', 0);
