<?php
session_start();
$currentWorkingDir=dirname(dirname(__FILE__));
$configDir=$currentWorkingDir . "/config";

if (realpath($configDir)) {
      set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
}

$globalConfFile='globalConfig.php';

require($globalConfFile);


if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $sessionTimeOut)) {
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    echo "-1";
}
else
{
    echo time() - $_SESSION['LAST_ACTIVITY'] ;
}

error_log("MESSAGE: " . $_SESSION['LAST_ACTIVITY']);

?>

