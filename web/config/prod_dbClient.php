<?php

$dbServer = 'toolbox.csd.toronto.ca';
$portNumber = "27017";
$connString="mongodb://" . $dbServer . ":" . $portNumber;

$client = new MongoDB\Client($connString);

?>
