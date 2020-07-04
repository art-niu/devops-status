<?php
session_start();

$currentWorkingDir=dirname(dirname(__FILE__));
$configDir=$currentWorkingDir . "/config";

if (realpath($configDir)) {
      set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
}

$globalConfFile='globalConfig.php';
require($globalConfFile);

//$sharedFunction = 'sharedFuncs.php';
//require ($sharedFunction);

//pushToLogFile("AUTH:" . $_SESSION['uid'] . "'s session timed out.");
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $sessionTimeOut)) {
    // last request was more than 30 minutes ago
    session_unset();     // unset $_SESSION variable for the run-time 
    session_destroy();   // destroy session data in storage
    //echo "-1";
}

$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

session_write_close(); 

//if(isset($_SESSION['uid'])) 
//  {
//   $userId = $_SESSION['uid'];
//   $userMail = $_SESSION['mail'];
//   $userFullName = $_SESSION['fullname'];
//  }
//else
//  {
   //header("Refresh:0; Location: /login.php");
//   header("Location: /login.php");
//  }
?>

