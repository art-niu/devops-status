<?php
$postdata = file_get_contents("php://input");

error_log(print_r($postdata, TRUE));

$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}

// Verify if the user logged in
$vSessionScript = 'verifySession.php';
$mongoDBConf = 'dbconf.php';
require_once ("sharedFuncs.php");
// require_once ($vSessionScript);
require_once ($mongoDBConf);

$statRecord = json_decode($postdata, TRUE);

$collectionName = $statRecord["collectionname"];
$primaryIndex = $statRecord["primaryindex"];

$collection = $dbName . "." . $collectionName;

// Remove collection name from array
if (($key = array_search($collectionName, $statRecord)) !== false) {
    unset($statRecord["collectionname"]);
}

$filterArray = array();
$filterArray["primaryindex"] = $primaryIndex;

$bulk = new MongoDB\Driver\BulkWrite();
$bulk->update($filterArray, $statRecord, [
    'multi' => false,
    'upsert' => true
]);

$writeConcern = new MongoDB\Driver\WriteConcern(0, 10000);
$result = $manager->executeBulkWrite($collection, $bulk, $writeConcern);

/* If a write could not happen at all */
foreach ($result->getWriteErrors() as $writeError) {
    printf("Operation#%d: %s (%d)\n", $writeError->getIndex(), $writeError->getMessage(), $writeError->getCode());
    $errMessage = $writeError->getMessage();
    error_log(print_r($errMessage, TRUE));
}

?> 

