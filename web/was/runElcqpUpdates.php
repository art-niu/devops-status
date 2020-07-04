<?php

$parentDir = dirname(dirname(__FILE__));
$commonDir = $parentDir . "/common";
$configDir = $parentDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}
// Verify if the user logged in
$vSessionScript='verifySession.php';
$mongoDBConf = 'dbconf.php';
require ("sharedFuncs.php");
require($vSessionScript);
require ($mongoDBConf);
require ("jobs.php");
//require ("disableBufferring.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    foreach ($_POST as $key => $value)
        eval("\$$key = \"$value\";");
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    $wasenv = trim($_GET['wasenv']);
}

use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;

$remoteServer = "csdvma.csd.toronto.ca";
$runAs = 'dboard';
pushToLogFile("Remote Server:" . $remoteServer);
$ssh = new SSH2($remoteServer);
$key = new RSA();
$key->loadKey(file_get_contents('/home/apache/.ssh/id_rsa'));

if (! $ssh->login($runAs, $key)) {
    exit('Login Failed');
}

$epocStart = time();
//$consolelog = $logfile;
//$consolelog = "runElcqpUpdates_" . $wasenv . "_" . $epocStart . ".log";
//$logFile = "/logs/" . $consolelog;
$runTaskCMD = "sudo /usr/local/elcqpQA/elcqp.sh "; 

$decryptScript = 'sudo bash -c "' . $runTaskCMD . '"';

pushToLogFile("Command Line: " . $decryptScript);


$newJob = new dynamicsJob();

$timestamp = date("Y-m-d H:i:s");

$jobId="ELCQP-" . $epocStart;

$newJob->jobId = $jobId;
$newJob->category = 'APPLICATION';
$newJob->task = 'ELCQP';
$newJob->cmd = $runTaskCMD;
$newJob->remoteServer = $remoteServer;
$newJob->runAs = $runAs;
$newJob->credential = 'SSH';
$newJob->userName = $runAs;
$newJob->startAt = $epocStart;
$newJob->finishAt = null;
$newJob->elapsed = null;
$newJob->status = NULL;
$newJob->logUrl = 'https://logs.csd.toronto.ca/tcsapps/dynamics/' . $consolelog;
$newJob->logFile = $logfile;
$newJob->consoleLog = $consolelog;

$jobStatus = $newJob->createUpdateJob();

ob_start();

error_log( print_r($GLOBALS['consolelog'], TRUE) );
$ssh->setTimeout(1800);
$outPut = $ssh->exec($runTaskCMD, function ($logLine) {
    //$epocStart = time();
    pushToSpecifiedLogFile($logLine, $GLOBALS['consolelog']);
    //echo $logLine . '<br>';
    //echo '\n';
    ob_flush();
    flush();
});

$exitStatus = $ssh->getExitStatus();
/*
if ( $exitStatus == 0 ) {
    echo "success";
} else {
    echo "failed";
}
*/

$epocEnd = time();
$elapsed = $epocEnd - $epocStart;

$newJob->finishAt = $epocEnd;
$newJob->elapsed = $elapsed;
$newJob->status = $exitStatus;

$jobStatus = $newJob->createUpdateJob();
?>
