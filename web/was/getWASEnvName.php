<?php
// Create connection to Oracle

session_start();
date_default_timezone_set('EST');

$currentWorkingDir=dirname(dirname(__FILE__));

$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}

// Verify if the user logged in
$vSessionScript='verifySession.php';

$mongoDBConf='dbconf.php';
require ("sharedFuncs.php");
//require($vSessionScript);
require($mongoDBConf);


$wasEnvName = trim($_GET['term']);
$wasEnvName = strtoupper($wasEnvName);

$dbName = 'tcsit';

$todayDate = date ( "Y-m-d" );

$regex = new \MongoDB\BSON\Regex ( '^' . $todayDate, 'm' );

$filter["envname"] = new \MongoDB\BSON\Regex($wasEnvName, 'i');
/*
$options = [
    // Only return the following fields in the matching documents 
    'projection' => [
        'envname' => 1,
        '_id' => 1,
    ],
    // Return the documents in descending order of views 
    'sort' => [
        'envname' => -1
    ],
];
*/
$options = [
    /* Return the documents in descending order of views */
    'sort' => [
        'envname' => -1
    ],
];

$query = new MongoDB\Driver\Query($filter, $options);

$readPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);
$cursor = $manager->executeQuery('tcsit.wasEnvironments', $query, $readPreference);

$return_array = array();

foreach ($cursor as $document ) {
    $row_array = (array)$document;
    $docArray['id'] = (string)$row_array['_id'];
    $docArray['value'] = $row_array['envname'];
    $docArray['label'] = $row_array['envname'];
    $docArray['prodline'] = $row_array['prodline'];
    $docArray['stage'] = $row_array['stage'];
    $docArray['srvname'] = $row_array['srvname'];
    $docArray['jvmname'] = $row_array['jvmname'];
    array_push($return_array, $docArray);
}

echo json_encode($return_array);

?>
