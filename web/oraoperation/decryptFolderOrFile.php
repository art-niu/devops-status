<?php

$parentDir = dirname(dirname(__FILE__));
$commonDir = $parentDir . "/common";
$configDir = $parentDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}
// Verify if the user logged in
// $vSessionScript='verifySession.php';
$mongoDBConf = 'dbconf.php';
require ("sharedFuncs.php");
// require($vSessionScript);
require ($mongoDBConf);

require __DIR__ . '/../vendor/autoload.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    foreach ($_POST as $key => $value)
        eval("\$$key = \"$value\";");
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
    echo "<strong>HTTP GET</strong> <br/><br/>";
    echo $_SERVER['QUERY_STRING'];
}

use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;

$remoteServer = $dstServerName;
$runAs = 'oracle10';
pushToLogFile("Remote Server:" . $remoteServer);
$ssh = new SSH2($remoteServer);
$key = new RSA();
$key->loadKey(file_get_contents('/home/apache/.ssh/id_rsa'));

if (! $ssh->login($runAs, $key)) {
    exit('Login Failed');
}

$epocStart = time();
$decryptCMD = "/usr/local/dba/decryptBackupNewerThanFile.sh " . $encryptedBackup . " " . $decryptedLocation . " " . $fileSet; 

$decryptScript = 'sudo bash -c "' . $decryptCMD . '"';

pushToLogFile("Command Line: " . $decryptScript);

require ("../common/jobs.php");
$newJob = new dynamicsJob();

$timestamp = date("Y-m-d H:i:s");

$jobId="DECRYPT-" . $epocStart;

$newJob->jobId = $jobId;
$newJob->category = 'ORABACKUP';
$newJob->task = 'DECRYPTORACLEBACKUP';
$newJob->cmd = $decryptCMD;
$newJob->remoteServer = $remoteServer;
$newJob->runAs = $runAs;
$newJob->credential = 'SSH';
$newJob->userName = $runAs;
$newJob->startAt = $epocStart;
$newJob->finishAt = null;
$newJob->elapsed = null;
$newJob->status = NULL;
$newJob->logUrl = 'https://logs.csd.toronto.ca/oracle/dynamics/' . $consoleLog;
$newJob->consoleLog = $consoleLog;

$jobStatus = $newJob->createUpdateJob();

$deploymentStatus = "Unknown";

ob_start();
error_log( print_r($GLOBALS['consoleLog'], TRUE) );
$ssh->setTimeout(1800);
$ssh->exec($decryptCMD, function ($logLine) {
    pushToSpecifiedLogFile($logLine, $GLOBALS['consoleLog']);
    flush();
    ob_flush();
});

$exitStatus = $ssh->getExitStatus();

$epocEnd = time();
$elapsed = $epocEnd - $epocStart;

$newJob->finishAt = $epocEnd;
$newJob->elapsed = $elapsed;
$newJob->status = $deploymentStatus;

$jobStatus = $newJob->createUpdateJob();
//echo $exitStatus;
$logStatusMsg = "Script Exit Code: " . $exitStatus;
error_log( print_r($GLOBALS['consoleLog'], TRUE) );

if  ( $exitStatus != 0 ) {
    echo ":FAILED";
} else {
    echo ":SUCCEEDED";
}
?>
