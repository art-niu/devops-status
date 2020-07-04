<?php
function flagUsedConfirmationCode($id) {

$requestFromX = isset($getenv['HTTP_X_FORWARDED_FOR']) ? $getenv['HTTP_X_FORWARDED_FOR'] : '';

if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
    $ip = $_SERVER['HTTP_CLIENT_IP'];
} elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
    $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
} else {
    $ip = $_SERVER['REMOTE_ADDR'];
}

    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    //return $ipaddress;

require("../config/dbconf.php");
$collection="tcsit.resetPasswordCodes";

$timestamp=date("Y-m-d H:i:s");

$bulk = new MongoDB\Driver\BulkWrite();
$mId = new MongoDB\BSON\ObjectId($id);
$bulk -> update(
    ['sindex' => $statusIndex, 'contentpage' => $contentPage ], 
    ['$push' => ['subscribers' => [ 'name' => $userFullName, 'email' => $userEmail ]]],
    ['multi' => false, 'upsert' => true]
);

//$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
$writeConcern = new MongoDB\Driver\WriteConcern(0, 10000);
$result = $manager->executeBulkWrite($collection, $bulk, $writeConcern);
/* If a write could not happen at all */
foreach ($result->getWriteErrors() as $writeError) {
    printf("Operation#%d: %s (%d)\n", $writeError->getIndex(), $writeError->getMessage(), $writeError->getCode());
    echo $writeError->getMessage();
}

}

?>
