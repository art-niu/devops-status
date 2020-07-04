<?php

//Connect to MongoDB server with Selfsigned Certificate

$SSL_FILE = "/etc/mongodb/ssl/client.pem";

//$dbServer = 'jenkins02.csd.toronto.ca';
$dbServer = '127.0.0.1';
$portNumber = "27017";
$connString="mongodb://" . $dbServer . ":" . $portNumber;
$dbName = 'tcsit';

$manager = new MongoDB\Driver\Manager(
    "mongodb://" . $dbServer . "/?ssl=true&authMechanism=MONGODB-X509",
    [
    ],
    [
        "pem_file" => $SSL_FILE,
        "peer_name" => $dbServer,
        "verify_peer" => false,
        "verify_peer_name" => false,
        "allow_self_signed" => true, 
    ]
);


?>
