<?php

$dbServer = 'qadboard.csd.toronto.ca';
$portNumber = "27017";
$connString="mongodb://" . $dbServer . ":" . $portNumber;

$client = new MongoDB\Client($connString);

?>
