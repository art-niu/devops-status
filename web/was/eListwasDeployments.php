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


$dbName = 'tcsit';

$todayDate = date("Y-m-d");

$command = new MongoDB\Driver\Command ( [
    'aggregate' => 'wasDeployments',
    'pipeline' => [
        [
            '$sort' => [
                'deploymentname' => 1,
                'appname' => 1
            ]
        ]
    ],
    'cursor' => new stdClass ()
] );
echo "<div id=\"eListwasDeployments\" >";
try {
    $cursor = $manager->executeCommand ( $dbName, $command );
    
    $rowIndex = 0;
    
    echo "<center>";
    echo "<table border=1 >";
    $rbgColor = "#FFFFFF";
    
    echo "<td></td><td><b>Deployment Name</b></td><td><b>Application Name</b></td><td><b>Ear File</b></td><td><b>Action</b></td>";
    
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
            . "<button id=\"delete_" . $value->deploymentname . "\" onClick=\"callCrudActionDeployment('delete','wasDeployments','" . $value->_id . "')\"><img src=\"/images/x.svg\" height=\"24\" width=\"24\" /> </button></td>" 
            . "<td>" . $value->deploymentname . "</td><td>" . $value->appname . "</td>" 
            . "<td><div id=\"pWASDEPLOYMENT_" . $value->_id . "\"> <input id=\"WASDEPLOYMENT_" . $value->_id
            . "\" type=\"text\" size=\"40\" value=\"" . $value->binary . "\" readonly=\"true\" ondblclick=\"showEditBox(this,'WASDEPLOYMENT_" . $value->_id . "','binary','wasDeployments')\"> </div>" 
            . "<div id=\"bWASDEPLOYMENT_" . $value->_id . "\"></div></td>". "<td><button onclick=\"deployApplication('" . $value->envname . " "  
            . $value->appname . " "  . $value->jvmname . " "  . $value->envname . " "  . $value->envname . "')\" id=\"" . $value->deploymentname . "\" > Deploy </button>";
        
        echo "</tr>";
  
        
    }
    echo "</table>";
    echo "</center>";
    echo "</div>";
    
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}
?>
