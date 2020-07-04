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

// Verify if the user logged in
$vSessionScript = 'verifySession.php';
$mongoDBConf = 'dbconf.php';

require_once ($mongoDBConf);
require ($vSessionScript);

$collectionName = 'tcsit.wasEnvironments';

$todayDate = date("Y-m-d");

$regex = new \MongoDB\BSON\Regex('^' . $todayDate, 'm');

$filter = [ 'envname' => ['$ne' => null ]];


$options = [
    /* Return the documents in descending order of views */
    'sort' => [
        'envname' => 1
    ]
];

$query = new MongoDB\Driver\Query($filter, $options);

$readPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);


try {
    
    $cursor = $manager->executeQuery($collectionName, $query, $readPreference);
    
    $rowIndex = 0;
    
    echo "<div id=\"eListwasEnvironments\" >";
    
    echo "<center>";
    echo "<table border=1 >";
    $rbgColor = "#FFFFFF";
    
    echo "<td></td><td><b>Environment Name</b></td><td><b>Product Line</b></td><td><b>Stage</b></td><td><b>Server Name</b></td><td><b>JVM Name</b></td>";
    
    foreach ($cursor as $document => $value) {
        if ($rowIndex == 0) {
            $rbgColor = "#CCEEFF";
            $rowIndex = 1;
        } else {
            $rbgColor = "#FFFFFF";
            $rowIndex = 0;
        }
        
        echo "<tr bgcolor=" . $rbgColor . ">";
        echo "<td>"
            . "<button id=\"delete_" . $value->envname . "\" onClick=\"callCrudAction('delete','wasEnvironments','" . $value->_id . "')\"><img src=\"/images/x.svg\" height=\"24\" width=\"24\" /> </button></td>" . 
        "<td>" . $value->envname . "</td><td>" . $value->prodline . "</td> <td>" . $value->stage . "</td><td>" . $value->srvname . "</td><td>" . $value->jvmname . "</td>";
        
        echo "</tr>";
        
    }
    echo "</table>";
    echo "</center>";
    echo "</div>";
    
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}
?>
