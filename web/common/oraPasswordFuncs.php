<?php
    
//Verify if confirmation code is valid    
function verifyConfirmationCode($confirmationCode) {
    //The function is to verify confirmation code before reset password
    
    $currentWorkingDir=dirname(dirname(__FILE__));
    $configDir=$currentWorkingDir . "/config";
    
    if (realpath($configDir)) {
      set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
    }
    
    $dbConfFile='dbconf.php';
    
    require($dbConfFile);
    
    $collection="tcsit.resetPasswordCodes";
    
    $currentTime=round(microtime(true));
    
    $filter = [
        'confirmationCode' => $confirmationCode,
        'resetAt' => '',
        'expiryTime' => [
            '$gte' => $currentTime,
        ],
    ];
    
    $options = [
        /* Only return the following fields in the matching documents */
        'projection' => [
            '_id' => 1,
            'userName' => 1,
            'userMail' => 1,
        ],
        ];
    
    $query = new MongoDB\Driver\Query($filter, $options);
    
    $readPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);
    try{
    $cursor = $manager->executeQuery('tcsit.resetPasswordCodes', $query, $readPreference);
    
    $referencecode=$cursor->toArray();
    
      if(count($referencecode) == 1){
          $_SESSION['cCode'] = $confirmationCode;
        foreach ($referencecode as $reference ) {
          $_SESSION['_id'] = $reference->_id;
          return $reference->userName;
        };
      } elseif(count($referencecode) > 1) {
        return "Multiple";
        $_SESSION['cCode'] = 'INVALID';
      } else {
        $_SESSION['cCode'] = 'INVALID';
         return "NotFound";
      }
    } catch (MongoDB\Driver\Exception\Exception $e) {
      echo $e->getMessage(), "\n";
    }
}

//Function update confirmation code status after user update passwords.

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
        ['_id' => $mId ], 
        ['$set' => ['resetAt' => $timestamp, 'resetFrom' => $ipaddress]],
        ['multi' => false, 'upsert' => false]
    );
    
    $writeConcern = new MongoDB\Driver\WriteConcern(0, 10000);
    $result = $manager->executeBulkWrite($collection, $bulk, $writeConcern);
    /* If a write could not happen at all */
    foreach ($result->getWriteErrors() as $writeError) {
        printf("Operation#%d: %s (%d)\n", $writeError->getIndex(), $writeError->getMessage(), $writeError->getCode());
        echo $writeError->getMessage();
    }

    //Clean up 
    $_SESSION['cCode'] = '';
    $_SESSION['_id'] = '';
    
}
    
?>
