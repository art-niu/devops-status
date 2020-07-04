<?php
// Create connection to Oracle
session_start();
date_default_timezone_set('EST');

$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}

$mongoDBConf = 'dbconf.php';
require ($mongoDBConf);

// Verify if the user logged in
$vSessionScript = 'verifySession.php';
$oraWalletEnv = 'oraWalletEnv.php';
//require ($vSessionScript);
require ($oraWalletEnv);
require_once ("sharedFuncs.php");

//use PHPMailer\PHPMailer\PHPMailer;
//require_once 'vendor/autoload.php';

//    $mail = new PHPMailer;
//$mail->setFrom('aniu@toronto.ca', 'Arthur Niu');

//$managerCopy = $manager;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value)
        eval("\$$key = \"$value\";");
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['db'])) {
        $db = $_GET['db'];
    }
}

switch ($db) {
    case "prod":
        $dbString = "ITSERVICE";
        $oraDbName = "V9PROD";
        break;

    case "qa":
        $dbString = "QADBOARD";
        $oraDbName = "CSISQA";
        break;

    default:
        $dbString = "DBDEV";
}

$to = array("dba", "infrastructure");
//$to = array("infrastructure");
$subject=$oraDbName . " Changes: ";
$mailBody="<table border=\"1\"><tr border=1><td>Database: " . $oraDbName . "</td><td>Last</td><td>Current</td></tr>";

$conn = oci_connect("/", "", $dbString, null, OCI_CRED_EXT);

//notifyByRoles($to, $subject, $message);

if (! $conn) {
    $m = oci_error();

    echo $m['message'] . "\n";
    exit();
} else {
    $sql = 'select CSISMON.monitor_pkg.GET_DB_STATUS from dual';
    $stmt = oci_parse($conn, $sql);

    $cursor = oci_new_cursor($conn);

    $return = oci_execute($stmt);

    if ($return) {

        oci_execute($cursor);

        $return_array = array();

        $row = oci_fetch_assoc($stmt);
        $dbStatus = $row["GET_DB_STATUS"];
        
        $statRecord = json_decode($dbStatus, TRUE);
        
        $collectionName = "oradbstatus";
        $primaryIndex = $statRecord["db_name"];
        
        $collection = $dbName . "." . $collectionName;
        
        // Get last update
        
        $filter = [
            'db_name' => $primaryIndex
        ];
        $options = [];
        
        $query = new MongoDB\Driver\Query($filter, $options);
        
        $readPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);
        $cursor = $manager->executeQuery($collection, $query, $readPreference);
        
        $statusChanged=false;
        
        if (count($cursor) == "1") {
            $returnArray = array();
            
            foreach ($cursor as $document) {
                $row_array = (array) $document;
                $docArray['host'] = $row_array['host'];
                $docArray['startup'] = $row_array['startup'];
                $docArray['status'] = $row_array['status'];
                $docArray['log_mode'] = $row_array['log_mode'];
                array_push($returnArray, $docArray);
            }
            
            $msgColor = "#0E8001";
            // Test if database status was changed
            if ( $docArray['host'] != $statRecord['host'] ) {
                $subject = $subject . " " . " Relocated, ";
                $msgColor = "#FF0000";
                $statusChanged=true;
            }
            
            $htmlMsg = "<tr><td>Running On</td><td>" . $docArray['host'] . "</td><td><font color=" . $msgColor . ">" . $statRecord['host'] . "</font></td></tr>";
            $mailBody = $mailBody . $htmlMsg;
            
            $msgColor = "#0E8001";
            if ( $docArray['startup'] != $statRecord['startup'] ) {
                $subject = $subject . " " . " Restarted, ";
                $msgColor = "#FF0000";
                $statusChanged=true;
            }
            $htmlMsg = "<tr><td>Start up @ </td><td>" . $docArray['startup'] . "</td><td><font color=" . $msgColor . ">" . $statRecord['startup'] . "</font></td></tr>";
            $mailBody = $mailBody . $htmlMsg;
            
            $msgColor = "#0E8001";
            if ( $docArray['status'] != $statRecord['status'] ) {
                $subject = $subject . " " . " Status, ";
                $msgColor = "#FF0000";
                $statusChanged=true;
            }
            $htmlMsg = "<tr><td>Status</td><td>" . $docArray['status'] . "</td><td><font color=" . $msgColor . ">" . $statRecord['status'] . "</font></td></tr>";
            $mailBody = $mailBody . $htmlMsg;
            
            $msgColor = "#0E8001";
            if ( $docArray['log_mode'] != $statRecord['log_mode'] ) {
                $subject = $subject . " " . " log_mode, ";
                $msgColor = "#FF0000";
                $statusChanged=true;
            }
            $htmlMsg = "<tr><td>Log Mode</td><td>" . $docArray['log_mode'] . "</td><td><font color=" . $msgColor . ">" . $statRecord['log_mode'] . "</font></td></tr>";
            $mailBody = $mailBody . $htmlMsg;

        } else {
            echo "Multiple Documents Found.";
        }
        
        $mailBody = $mailBody . "</table>" . "<br>";

        echo $mailBody;
        // Notify Administrators for Status Changes
        if ($statusChanged) {
            echo "Database Status Changed." ;
            notifyByRoles($to, $subject, $mailBody);
        }
        
        // Update DB status collection
        $filterArray = array();
        $filterArray["db_name"] = $primaryIndex;
        
        $statRecord['updateat'] = time();
        //$statRecord['status'] = "MOUNT";
        
        $bulkSet['$set'] =  $statRecord;
        $bulk = new MongoDB\Driver\BulkWrite();
        $bulk->update($filterArray, $bulkSet, [
            'multi' => false,
            'upsert' => true
        ]);
        $writeConcern = new MongoDB\Driver\WriteConcern(0, 10000);
        $result = $manager->executeBulkWrite($collection, $bulk, $writeConcern);
        foreach ($result->getWriteErrors() as $writeError) {
            printf("Operation#%d: %s (%d)\n", $writeError->getIndex(), $writeError->getMessage(), $writeError->getCode());
            $errMessage = $writeError->getMessage();
            error_log(print_r($errMessage, TRUE));
        }
        //echo "success";
    } else {
        echo "Failed to get " . $db . " database status, please contact DBA.";
    }
}

// Close the Oracle connection and resources
oci_free_statement($stmt);
//oci_free_statement($cursor);
oci_close($conn);
?>
