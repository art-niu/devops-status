<?php

$dbase      = 'dbqa.csd.toronto.ca:1521/csisqa';

$dbase      = 'csis2.csd.toronto.ca:1521/v9prod';
$dbSrv      = 'csis2.csd.toronto.ca';
$dbLsnPort      = '1521';
$dbServiceName = 'V9PROD';

$dbstr ="(DESCRIPTION =(ADDRESS = (PROTOCOL = TCP)(HOST = " . $dbSrv . ")(PORT = " . $dbLsnPort . "))
(CONNECT_DATA =
(SERVER = DEDICATED)
(SERVICE_NAME = " . $dbServiceName . ")
))";

?>

