<?php

$logPath = '/usr/share/dboard/logs';
$logFile = 'dboard.log';

$headers = 'From: TCS IT<noreply@csd.toronto.ca>' . "\r\n" .
   'Reply-To: noreply@csd.toronto.ca' . "\r\n" .
   'X-Mailer: PHP/' . phpversion();
$specialUserRoles = array('infrastructure', 'helpdesk', 'developer');
?>
