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

$userName = trim($_GET['term']);
$userName = strtolower($userName);

require __DIR__ . '/../vendor/autoload.php';

use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;

$remoteServer = 'tcsfs.csd.toronto.ca';
$runAs = 'dboard';

$ssh = new SSH2($remoteServer);
$key = new RSA();
$key->loadKey(file_get_contents('/home/apache/.ssh/id_rsa'));

if (! $ssh->login($runAs, $key)) {
    exit('Login Failed');
}

$epocStart = time();
$consoleLog = "get_file_server_username.log";
// $logFile = "/devZone/logs/secure9train/deployment/" . $consoleLog;

$shellCMD = "grep " . $userName . " /etc/passwd";

$ssh->setTimeout(1800);
$cmdOutPut = $ssh->exec($shellCMD);
// $cmdOutPut = $ssh->exec($shellCMD, function ($logLine) {
// pushToSpecifiedLogFile($logLine, $GLOBALS['consoleLog']);
// echo $logLine . '<br>';
// $deploymentStatusREx="/^DeploymentStatus: */";
// if (preg_match($deploymentStatusREx,$logLine)) {
// $pair = explode(",", $logLine);
// $GLOBALS['deploymentStatus'] = $pair[1];
// }
// flush();
// ob_flush();

// });
//

$returnArray = array();

if (isset($cmdOutPut)) {
    $userList = explode(PHP_EOL, $cmdOutPut);
    foreach ($userList as &$value) {
        if (! empty($value)) {
            $tmpArray = explode(":", $value);
            $simpleArray['id'] = $tmpArray[0];
            if (empty($tmpArray[4])) {
                $simpleArray['value'] = $tmpArray[0];
                
            } else {
                $simpleArray['value'] = $tmpArray[4];
            }
            $simpleArray['label'] = $simpleArray['value'];
            array_push($returnArray, $simpleArray);
        }
    }
    echo json_encode($returnArray);
}





?>
