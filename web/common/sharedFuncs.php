<?php
session_start();

// Verify if confirmation code is valid
function notifyUser($to, $subject, $message)
{
    $currentWorkingDir = dirname(dirname(__FILE__));
    $configDir = $currentWorkingDir . "/config";

    if (realpath($configDir)) {
        set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
    }

    $globalConfFile = 'globalConfig.php';

    require ($globalConfFile);

    $status = mail($to, $subject, $message, $headers, "-f " . $from);
}

// Append to log file
function pushToLogFile($message)
{
    $currentWorkingDir = dirname(dirname(__FILE__));
    $logDir = $currentWorkingDir . "/logs/";
    $configDir = $currentWorkingDir . "/config";
    $logFile = $logDir . 'tcs_log';
    date_default_timezone_set('US/Eastern');
    $date = date("Y/m/d h:i:s H");

    if (realpath($configDir)) {
        set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
    }
    $globalConfFile = 'globalConfig.php';
    require ($globalConfFile);

    file_put_contents($logFile, $date . " - " . $_SESSION['uid'] . " - " . $message . "\n", FILE_APPEND | LOCK_EX);
}

function cmd_exec($cmd, &$stdout, &$stderr)
{
    $outfile = tempnam(".", "cmd");
    $errfile = tempnam(".", "cmd");
    $descriptorspec = array(
        0 => array(
            "pipe",
            "r"
        ),
        1 => array(
            "file",
            $outfile,
            "w"
        ),
        2 => array(
            "file",
            $errfile,
            "w"
        )
    );
    $proc = proc_open($cmd, $descriptorspec, $pipes);

    if (! is_resource($proc))
        return 255;

    fclose($pipes[0]); // Don't really want to give any input

    $exit = proc_close($proc);
    $stdout = file($outfile);
    $stderr = file($errfile);

    unlink($outfile);
    unlink($errfile);
    return $exit;
}

function killRemoteSession($serverName, $sessionID)
{
    $currentWorkingDir = dirname(dirname(__FILE__));
    $configDir = $currentWorkingDir . "/config";

    if (realpath($configDir)) {
        set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
    }

    $globalConfFile = 'globalConfig.php';

    require ($globalConfFile);

    $commandLine = $scriptLocation . "killIASSession.sh" . " " . $jobRunner . " " . $serverName . " " . $sessionID;

    $returnCode = cmd_exec($commandLine, $results, $error);
    $json = array();
    foreach ($results as $result) {
        $json[] = intval(str_replace(array(
            "\r",
            "\n"
        ), '', $result));
    }
    // echo json_encode($json);
    // pushToLogFile(json_encode($json));
    return $returnCode;
}

function convertMongoTime2LocalTime($mongoTime)
{
    $datetime = $mongoTime->toDateTime();
    $time = $datetime->format(DATE_RSS);
    $dateInUTC = $time;
    $time = strtotime($dateInUTC . ' UTC');
    $timeInLocal = date("Y-m-d H:i:s", $time);
    return $timeInLocal;
}

// Append to log file
function pushToSpecifiedLogFile($message, $logFileName)
{
    $currentWorkingDir = dirname(dirname(__FILE__));
    $logDir = $currentWorkingDir . "/logs/";
    $configDir = $currentWorkingDir . "/config";
    $logFile = $logDir . $logFileName;
    date_default_timezone_set('US/Eastern');
    $date = date("Y/m/d H:i:s H");

    if (realpath($configDir)) {
        set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
    }
    $globalConfFile = 'globalConfig.php';
    require ($globalConfFile);

    file_put_contents($logFile, $date . " - " . $_SESSION['uid'] . " - " . $message . "\n", FILE_APPEND | LOCK_EX);
}

// Set _SESSION['roles']
function setUserRoles()
{
    $currentWorkingDir = dirname(dirname(__FILE__));
    $configDir = $currentWorkingDir . "/config";
    if (realpath($configDir)) {
        set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
    }
    // Verify if the user logged in
    $mongoDBConf = 'dbconf.php';
    require ($mongoDBConf);
    $tgtCollection = "tcsit.userroles";

    $uid = $_SESSION['uid'];

    $filter = [
        'userid' => $uid
    ];
    $options = [];

    $query = new MongoDB\Driver\Query($filter, $options);

    $readPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);
    $cursor = $manager->executeQuery($tgtCollection, $query, $readPreference);

    if (count($cursor) == "1") {
        $returnArray = array();

        foreach ($cursor as $document) {
            $rowArray = (array) $document;
            $docArray['active'] = $rowArray['active'];
            $docArray['roles'] = $rowArray['roles'];
            $docArray['mypage'] = $rowArray['mypage'];
            array_push($returnArray, $docArray);
        }

        // echo json_encode($docArray['roles']);
        $_SESSION['roles'] = $docArray['roles'];
        $_SESSION['mypage'] = $docArray['mypage'];
    } else {
        $_SESSION['roles'] = 'UNKNOWN';
        // echo "Multiple Documents Found.";
    }
}

// Notify Users by Roles
require_once "vendor/autoload.php";
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function notifyByRoles(Array $tgtRoles,$subject,$message)
{
    $currentWorkingDir = dirname(dirname(__FILE__));
    $configDir = $currentWorkingDir . "/config";
    if (realpath($configDir)) {
        set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
    }
    // Verify if the user logged in
    $mongoDBConf = 'dbconf.php';
    require ($mongoDBConf);
    $tgtCollection = "tcsit.userroles";
    
    $regExp = "";

    $selectedRoles = array();
    foreach ($tgtRoles as $role) {
        $roleRegExp = new MongoDB\BSON\Regex($role, 'i');
        $$role['roles'] = $roleRegExp;
        array_push($selectedRoles, $$role);
    }

$filter['$or'] =  $selectedRoles;

    $options = [
        'projection' => [
            'fullname' => 1,
            'email' => 1
        ]
    ];

    $query = new MongoDB\Driver\Query($filter, $options);

    $readPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);
    $cursor = $manager->executeQuery($tgtCollection, $query, $readPreference);

    $mail = new PHPMailer(true);
    $mail->Host = 'ex90.csd.toronto.ca';
    //Set who the message is to be sent from
    $mail->setFrom('aniu@toronto.ca', 'Arthur Niu');
    //Set an alternative reply-to address
    $mail->addReplyTo('aniu@toronto.ca', 'Arthur Niu');
    //Set who the message is to be sent to
    $mail->addAddress('aniu@toronto.ca', 'Arthur Niu');
$mail->Priority = "1";
    
    //$returnArray = array();
    foreach ($cursor as $document) {
        $rowArray = (array) $document;

if ( ! empty($rowArray['email'])) {
        $mail->addAddress($rowArray['email'], $rowArray['fullname']);
       } 
    }


    $mail->Subject = $subject;
    $mail->msgHTML($message, __DIR__);
    
    if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo;
    } else {
       echo "Message sent!";
    }
    
    //return $returnArray;
}

?>
