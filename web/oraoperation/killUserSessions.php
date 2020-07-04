<?php
session_start();
date_default_timezone_set('EST');

$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir);
}
// Verify if the user logged in
$vSessionScript = 'verifySession.php';
$oraWalletEnv = 'oraWalletEnv.php';
require ("sharedFuncs.php");
require ($vSessionScript);
require ($oraWalletEnv);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value) {
        $selectedSessions = $value;
    }
    if (empty($selectedSessions)) {
        echo "empty";
        exit();
    }
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "<strong>HTTP GET</strong> <br/><br/>";
    echo $_SERVER['QUERY_STRING'];
}

if (isset($_SESSION['dbString'])) {
    $dbString = $_SESSION['dbString'];
}
if (isset($_SESSION['dbString'])) {
    $dbString = $_SESSION['dbString'];
}

// $conn = oci_connect("/", "", "ITSERVICE", null, OCI_CRED_EXT);
$conn = oci_connect("/", "", $dbString, null, OCI_CRED_EXT);

if (! $conn) {
    $m = oci_error();
    
    echo $m['message'], "\n";
    exit();
} else {
    $sql = 'BEGIN administer_users.KILL_OWN_SESSION(:P_INVOKER_USERNAME, :P_TARGET_USERNAME, :P_TARGET_SID, :P_TARGET_SERIAL); END;';
    $stmt = oci_parse($conn, $sql);
    
    foreach ($selectedSessions as &$value) {
        $pair = explode(",", $value);
        echo $pair[0] . ":" . $pair[1] . ":" . $pair[2] . "\n";
        // Bind the input parameter $_SESSION['uid']
        oci_bind_by_name($stmt, ":P_INVOKER_USERNAME", $_SESSION['uid']);
        oci_bind_by_name($stmt, ":P_TARGET_USERNAME", $pair[0]);
        oci_bind_by_name($stmt, ":P_TARGET_SID", $pair[1]);
        oci_bind_by_name($stmt, ":P_TARGET_SERIAL", $pair[2]);
        
        $return = oci_execute($stmt);
        if ($return) {
            pushToLogFile($pair[0] . "'s session " .  $pair[1] . " has been killed by " . $_SESSION['uid']);
            echo "success";
        } else {
            $m = oci_error();
            echo $m;
        }
    } // if
} // else
  
// Close the Oracle connection
oci_free_statement($stmt);
oci_close($conn);
?>
