<?php
// Create connection to Oracle
date_default_timezone_set('EST');

$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir);
}
// Verify if the user logged in
$vSessionScript = 'verifySession.php';
$oraWalletEnv = 'oraWalletEnv.php';
require ($vSessionScript);
require ($oraWalletEnv);

$userName = trim($_GET['term']);
$userName = strtoupper($userName);

if (isset($_SESSION['dbString'])) {
    $dbString = $_SESSION['dbString'];
}

error_log("Error: " . $dbString, 0);

// Set the environment variables required Oracle Wallet Authentication
// putenv("TNS_ADMIN=/opt/.wallet");
// putenv("ORACLE_HOME=/usr2/app/oracle/product/11.2.0.3");
// putenv("LD_LIBRARY_PATH=/usr2/app/oracle/product/11.2.0.3/lib");

$conn = oci_connect("/", "", $dbString, null, OCI_CRED_EXT);

if (! $conn) {
    $m = oci_error();
    
    echo $m['message'] . "\n";
    exit();
} else {
    $sql = 'BEGIN administer_users.GET_ACCOUNT_STATUSES(:P_OUT,:P_USERNAME); END;';
    $stmt = oci_parse($conn, $sql);
    
    // Bind the output parameter
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":P_USERNAME", $userName);
    oci_bind_by_name($stmt, ":P_OUT", $cursor, - 1, OCI_B_CURSOR);
    
    $return = oci_execute($stmt);
    if ($return) {
        
        oci_execute($cursor);
        
        $return_array = array();
        session_start();
        
        while (($row = oci_fetch_array($cursor, OCI_BOTH)) != false) {
            $row_array['id'] = $row['USERNAME'];
            $row_array['value'] = $row['USERNAME'];
            $row_array['label'] = $row['USERNAME'];
            
            // array_push($return_array,$row['USERNAME'],$row['ACCOUNT_STATUS']);
            array_push($return_array, $row_array);
            $userStatus = $row['USERNAME'] . ":" . $row['ACCOUNT_STATUS'];
            $_SESSION['orauserstatus'] = $userStatus;
        }
        
        session_write_close(); 
        echo json_encode($return_array);
    } else {
        echo "Failed to get user status, please contact DBA.";
    }
}

// Close the Oracle connection and resources
oci_free_statement($stmt);
oci_free_statement($cursor);
oci_close($conn);
?>
