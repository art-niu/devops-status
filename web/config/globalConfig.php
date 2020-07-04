<?php
// Notification Mechanism
$fromEmail = "tcsithelp@toronto.ca";
$fromName = "TCS IT Infrastructure";
$header  = "From: $fromEmail";
$replyTo = "aniu@toronto.ca";

$sessionTimeOut=2 * 60 * 60; // Session Time Out in 2 hours
$jobRunner = "dboard";
$apacheKeyFile="/home/apache/.ssh/id_rsa";
$apachePubKeyFile="/home/apache/.ssh/id_rsa.pub";
$scriptLocation="/usr/share/dboard/scripts/";
$sshMethods = array(
    'kex' => 'diffie-hellman-group1-sha1',
    'client_to_server' => array(
        'crypt' => 'aes256-cbc',
        'comp' => 'none',
        'mac' => 'hmac-sha1'),
    'server_to_client' => array(
        'crypt' => 'aes256-cbc',
        'comp' => 'none',
        'mac' => 'hmac-sha1'));
?>
