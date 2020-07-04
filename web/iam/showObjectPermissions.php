<?php

session_start();
date_default_timezone_set('EST');

$currentWorkingDir=dirname(dirname(__FILE__));
$commonDir=$currentWorkingDir . "/common";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir);
}
// Verify if the user logged in
$vSessionScript='verifySession.php';
$oraWalletEnv='oraWalletEnv.php';
require("sharedFuncs.php");
require($vSessionScript);
require($oraWalletEnv);

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    foreach ($_POST as $key => $value) {
        $selectedSessions = $value;
    }
    if ( empty($selectedSessions)) {
        echo "empty";
        exit;
    }
    
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    echo "<strong>HTTP GET</strong> <br/><br/>";
    echo $_SERVER['QUERY_STRING'];
}

//array(1) { [0]=> string(43) "ANIU,696,3093,13949,frmweb@sun2 (TNS V1-V3)" }

$programREx="/^frmweb@([0-9A-z]+) */";

// Remove duplicated sessions
foreach ( $selectedSessions as &$value) {
    $pair = explode(",", $value);
    if (preg_match($programREx,$pair[4],$matches)) {
        $hash = $pair[3];
        $unique_array[$hash][0] = $pair[0]; //username
        $unique_array[$hash][1] = $pair[3]; //session number
        $unique_array[$hash][2] = $matches[1]; //server name
    }
} //foreach

foreach ( $unique_array as $session) {
    $logMsg= _SESSOPM['uid'] . " - KILLSESSION: " . $session[1] . " on server " . $session[2] . " for user " . $session[0];
    pushToLogFile($logMsg);
    $returnCode=killRemoteSession($session[2], $session[1]);
    //var_dump($returnCode);
    echo $returnCode;
} //foreach

?>
