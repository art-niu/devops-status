<?php

$dbase      = 'dbqa.csd.toronto.ca:1521/csisqa';

$dbSrv      = 'dbqa.csd.toronto.ca';
$dbLsnPort      = '1521';
$dbServiceName = 'CSISQA';

$dbstr ="(DESCRIPTION =(ADDRESS = (PROTOCOL = TCP)(HOST = " . $dbSrv . ")(PORT = " . $dbLsnPort . "))
(CONNECT_DATA =
(SERVER = DEDICATED)
(SERVICE_NAME = " . $dbServiceName . ")
))";

?>

