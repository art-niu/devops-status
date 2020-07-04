<?php
session_start();
date_default_timezone_set('EST');

$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}

// Verify if the user logged in
$vSessionScript = 'verifySession.php';

require ($vSessionScript);

$commonFunctions = 'oraPasswordFuncs.php';

require ("config.php");
require ($commonFunctions);
require ("sharedFuncs.php");

require ("oraWalletEnv.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value)
        eval("\$$key = \"$value\";");
    // echo $key.'='.$value.'<br />';
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "<strong>HTTP GET</strong> <br/><br/>";
    echo $_SERVER['QUERY_STRING'];
}

// Check confirmation code validity

$userName = strtoupper($userName);

if (isset($_SESSION['dbString'])) {
    $dbString = $_SESSION['dbString'];
}

$conn = oci_connect("/", "", $dbString, null, OCI_CRED_EXT);

if (! $conn) {
    $m = oci_error();
    
    echo $m['message'], "\n";
    exit();
} else {
    $sql = 'BEGIN administer_users.change_password(:P_USERNAME, :P_NEW_PASSWORD); END;';
    $stmt = oci_parse($conn, $sql);
    
    // Bind the input parameter
    oci_bind_by_name($stmt, ":P_USERNAME", $userName);
    oci_bind_by_name($stmt, ":P_NEW_PASSWORD", $newPWD);
    
    $return = oci_execute($stmt, OCI_COMMIT_ON_SUCCESS);
    
    if ($return) {
        // Update confirmation record
        //flagUsedConfirmationCode($_SESSION['_id']);
        $subject = "Your Oracle password has been reset successfully";
        
        $messageBody = "Dear " . $userFullName . " , \n\rYour Oracle password has been reset successfully. \n\rIf you are NOT aware of this change, please report to:\n\r tcsithelp@toronto.ca \n\r 416-397-5555 \n\r IT of Toronto Children's Services\n\r";
        //include '/oraoperation/getOraUserStatus.php';
        echo "success";
        mail($userMail, $subject, $messageBody, $headers);
        pushToLogFile($userName . "'s password has been reset.");
        
    } else {
        $e = oci_error($stmt); // For oci_parse errors pass the connection handle
                               // trigger_error(htmlentities($e['message']), E_USER_ERROR);
        pushToLogFile("ERROR: " . $e['message']);
        
        echo "ERROR: " . $e['message'];
    }
}

// Close the Oracle connection
oci_free_statement($sql);
oci_free_statement($stmt);
oci_close($conn);
?>
