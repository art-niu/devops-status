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
// require ($vSessionScript);
require ($oraWalletEnv);
require_once ("sharedFuncs.php");

// use PHPMailer\PHPMailer\PHPMailer;
// require_once 'vendor/autoload.php';

// $mail = new PHPMailer;
// $mail->setFrom('aniu@toronto.ca', 'Arthur Niu');

// $managerCopy = $manager;
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

$to = array(
    "dba",
    "infrastructure"
);
// $to = array("infrastructure");
$subject = $oraDbName . " Changes: ";

$conn = oci_connect("/", "", $dbString, null, OCI_CRED_EXT);

// notifyByRoles($to, $subject, $message);

if (! $conn) {
    $m = oci_error();

    echo $m['message'] . "\n";
    exit();
} else {
    $sql = 'select CSISMON.monitor_pkg.GET_ASM_DISKGROUPS_STATUS from dual';
    $stmt = oci_parse($conn, $sql);

    $cursor = oci_new_cursor($conn);

    $return = oci_execute($stmt);

    if ($return) {

        oci_execute($cursor);

        $return_array = array();

        $row = oci_fetch_assoc($stmt);
        $dbStatus = $row["GET_ASM_DISKGROUPS_STATUS"];

        $statRecord = json_decode($dbStatus, TRUE);
        $collectionName = "oraRAC1ASMStatus" . "_" . $db;
        $primaryIndex = $diskGroup["name"];

        $collection = $dbName . "." . $collectionName;

        foreach ($statRecord["asm_diskgroups"] as $diskGroup) {
            $diskGroup['total_GB'] = $diskGroup['total_MB'] / 1024;
            $diskGroup['free_GB'] = $diskGroup['free_MB'] / 1024;

            $tableBody = "<table border=\"1\"><tr border=1><td>Database</td><td> " . $oraDbName . "</td></tr>";
            $msgColor = "#CCEEFF";

            $htmlMsg = "<tr><td>Disk Group Name </td><td bgcolor=" . $msgColor . ">" . $diskGroup['name'] . "</td></tr>";
            $tableBody = $tableBody . $htmlMsg;

            $htmlMsg = "<tr><td>Collected @ </td><td>" . $diskGroup['time'] . "</td></tr>";
            $tableBody = $tableBody . $htmlMsg;

            $htmlMsg = "<tr><td>Total</td><td>" . $diskGroup['total_GB'] . " GB </td></tr>";
            $tableBody = $tableBody . $htmlMsg;

            $htmlMsg = "<tr><td>Free</td><td>" . $diskGroup['free_GB'] . " GB </td></tr>";
            $tableBody = $tableBody . $htmlMsg;

            $tableBody = $tableBody . "</table>" . "<br>";

            echo $tableBody;

            // Update DB status collection
            $filterArray = array();
            $filterArray["name"] = $primaryIndex;

            $diskGroup['updateat'] = time();
            unset($diskGroup['total_MB']);
            unset($diskGroup['free_MB']);
            $bulkSet['$set'] = $diskGroup;
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
            // echo "success";
        }
    } else {
        echo "Failed to get " . $db . " database status, please contact DBA.";
    }
}

// Close the Oracle connection and resources
oci_free_statement($stmt);
// oci_free_statement($cursor);
oci_close($conn);
?>
