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
$mongoDBConf = 'dbconf.php';
require_once ("sharedFuncs.php");
require_once ($vSessionScript);
require_once ($mongoDBConf);

$wasEnvName = trim($_GET['deploymentname']);
//$wasEnvName = strtoupper($wasEnvName);

$collectionName = 'tcsit.wasDeployments';

$todayDate = date("Y-m-d");

$filter["deploymentname"] = $wasEnvName;
/*
 * $options = [
 * // Only return the following fields in the matching documents
 * 'projection' => [
 * 'envname' => 1,
 * '_id' => 1,
 * ],
 * // Return the documents in descending order of views
 * 'sort' => [
 * 'envname' => -1
 * ],
 * ];
 */
$options = [
    /* Return the documents in descending order of views */
    'sort' => [
        'deploymentname' => - 1
    ]
];

$query = new MongoDB\Driver\Query($filter, $options);

$readPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);
try {
    
    $cursor = $manager->executeQuery($collectionName, $query, $readPreference);
    
    $rowIndex = 0;
    echo "<center>";
    echo "<table border=1 >";
    $rbgColor = "#FFFFFF";
    
    echo "<td><b>Deployment Name</b></td><td><b>Application Name</b></td>";
    
    foreach ($cursor as $document => $value) {
        if ($rowIndex == 0) {
            $rbgColor = "#CCEEFF";
            $rowIndex = 1;
        } else {
            $rbgColor = "#FFFFFF";
            $rowIndex = 0;
        }
        
        if ($value->node == "sun01") {
            $rbgColor = "#FF0000";
        }
        echo "<tr bgcolor=" . $rbgColor . ">";
        echo "<td>" . $value->deploymentname . "</td><td>" . $value->appname . "</td><td><button onclick=\"deployApplication('" . $value->evnname . " "  . $value->appname . " "  . $value->jvmname . " "  . $value->evnname . " "  . $value->evnname . "')\" id=" . $value->deploymentname . " Deploy />";
        
        echo "</tr>";

    }
    echo "</table>";
    echo "</center>";
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}
?>
