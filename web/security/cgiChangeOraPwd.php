<?php

session_start();

// Verify if the user logged in
$vSessionScript='/common/verifySession.php';
if (!file_exists($vSessionScript)) {
  $vSessionScript='../common/verifySession.php';
}

require($vSessionScript);

$dbConfFile='../config/oraConnection.php';

if (!file_exists($dbConfFile)) {
    $dbConfFile='/config/oraConnection.php';
}

include_once($dbConfFile);

    $currentWorkingDir=dirname(dirname(__FILE__));
    $configDir=$currentWorkingDir . "/common";

    if (realpath($configDir)) {
      set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
    }

    $sharedFunction='sharedFuncs.php';

    require($sharedFunction);

$connMessage='';

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

// Connect to Oracle RAC
// Status : Failure -Test failed: ORA-01017: invalid userName/userPWD; logon denied
if(!@($conn = oci_connect($userId, $userPWD, $dbstr)))
{
 $e = oci_error();   // For oci_connect errors pass no handle
 $connMessage=$e['message'];
}

if (!$conn) {
  echo $connMessage;
} else {
        $conn = oci_password_change($conn, $userId, $userPWD, $newPWD);
        if ($conn) {
            $subject="Your Oracle password has been changed successfully";

            $messageBody="Dear " . $userFullName . " , \n\rYour Oracle password has been changed successfully. \n\rIf you DID NOT change your Oracle password, please report to:\n\r csdithelp@toronto.ca \n\r 416-397-5555 \n\r Helpdesk of Toronto Children's Services\n\r";

            notifyUser($userMail,$subject,$messageBody);
            echo 'success';

        } else {
	    echo "Failed to change your password.\n";
	}
}

?>

