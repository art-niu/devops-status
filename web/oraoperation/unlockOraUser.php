<?php
// Create connection to Oracle
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
    foreach ($_POST as $key => $value)
        eval("\$$key = \"$value\";");
    // echo $key.'='.$value.'<br />';
}

if (isset($_SESSION['dbString'])) {
    $dbString = $_SESSION['dbString'];
}

$conn = oci_connect("/", "", $dbString, null, OCI_CRED_EXT);

# $conn = oci_connect("/", "", "ITSERVICE", "", OCI_CRED_EXT);

if (! $conn) {
    $m = oci_error();
    
    echo $m['message'] . "\n";
    pushToLogFile($m['message']);
    exit();
} else {
    $sql = 'BEGIN administer_users.unlock_user(:P_USERNAME); END;';
    $stmt = oci_parse($conn, $sql);
    
    // Bind the output parameter
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":P_USERNAME", $userName);
    
    $return = oci_execute($stmt);
    if ($return) {
        echo "SUCCESS";
        $userStatus = $userName . ":" . "OPEN";
        $_SESSION['orauserstatus'] = $userStatus;
        pushToLogFile($userName . " has been unclocked.");
    } else {
        
        $e = oci_error($stmt); // For oci_parse errors pass the connection handle
        // trigger_error(htmlentities($e['message']), E_USER_ERROR);
        pushToLogFile("ERROR: " . $e['message']);
        
        echo "ERROR: " . $e['message'];
        
        //echo "Failed to unlock Oracle User Account " . $userName . ", please contact DBA.";
    }
}

// Close the Oracle connection and resources
oci_free_statement($stmt);
oci_free_statement($cursor);
oci_close($conn);
?>
