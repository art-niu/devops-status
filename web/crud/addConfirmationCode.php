<?php

session_start();

// Verify if the user logged in
$vSessionScript='verifySession.php';

$currentWorkingDir=dirname(dirname(__FILE__));
$commonDir=$currentWorkingDir . "/common";
$configDir=$currentWorkingDir . "/config";
if (realpath($commonDir)) {
  set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}

require($vSessionScript);
require("config.php");

$action = isset($_POST['action']) ? $_POST['action'] : 'add';

date_default_timezone_set('EST');

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

$bulk = new MongoDB\Driver\BulkWrite();
if(!empty($action)) {
        switch($action) {
                case "add":
			$confirmationCode=uniqid();
			$timestamp=date("Y-m-d H:i:s");
			$expiryTime=round(microtime(true)) + 86400000;
			$bulk->insert([
				'userName' => $userId,
				'userMail' => $userMail,
				'confirmationCode' => $confirmationCode,
				'timestamp' => $timestamp,
				'expiryTime' => $expiryTime,
				'requestFrom' => $_SERVER['REMOTE_ADDR'],
				'requestFromX' => $requestFromX,
				'resetAt' => '',
				'resetFrom' =>''
			]);
			$writeConcern = new MongoDB\Driver\WriteConcern(0, 10000);

			try {
    				$result = $manager->executeBulkWrite($collection, $bulk, $writeConcern);
			} catch (MongoDB\Driver\Exception\BulkWriteException $e) {
    				$result = $e->getWriteResult();

    				// Check if the write concern could not be fulfilled
    				if ($writeConcernError = $result->getWriteConcernError()) {
        				printf("%s (%d): %s\n",
            					$writeConcernError->getMessage(),
            					$writeConcernError->getCode(),
            					var_export($writeConcernError->getInfo(), true)
        				);
    			}

    			// Check if any write operations did not complete at all
    			foreach ($result->getWriteErrors() as $writeError) {
        			printf("Operation#%d: %s (%d)\n",
            				$writeError->getIndex(),
            				$writeError->getMessage(),
            				$writeError->getCode()
        			);
    			}
			} catch (MongoDB\Driver\Exception\Exception $e) {
    				printf("Other error: %s\n", $e->getMessage());
    				exit;
			}
			$subject="Confirmation to RESET Your Oracle Password";

			$messageBody="Dear " . $userFullName . " , \n\rYou recently requested to reset your Oracle Password, please click below link to confirm: \n https://" . $_SERVER['HTTP_HOST'] . "/index.php?load=security/forgetOraPasswd.php&cCode=" . $confirmationCode . " \n\rPlease be aware that the link will expire in 24 hours. \n\rIf you didn't request password reset, please report to:\n\r csdithelp@toronto.ca \n\r 416-397-5555 \n\r IT of Toronto Children's Services\n\r";

			mail($userMail,$subject,$messageBody,$headers);

			echo "Confirmation email has been sent to " . $userMail . ". Please check your mailbox.";

                        break;

                case "edit":
$mId = new MongoDB\BSON\ObjectId($id);
$bulk -> update(
    ['_id' => $mId ], 
    ['$set' => ['appurl' => $appUrl]],
    ['multi' => false, 'upsert' => false]
);

//$manager = new MongoDB\Driver\Manager('mongodb://localhost:27017');

//$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
$writeConcern = new MongoDB\Driver\WriteConcern(0, 10000);
$result = $manager->executeBulkWrite($collection, $bulk, $writeConcern);
                        if($result){
                                  echo $appUrl;
                       }
/* printf("Inserted %d document(s)\n", $result->getInsertedCount());
printf("Matched  %d document(s)\n", $result->getMatchedCount());
printf("Updated  %d document(s)\n", $result->getModifiedCount());
printf("Upserted %d document(s)\n", $result->getUpsertedCount());
printf("Deleted  %d document(s)\n", $result->getDeletedCount());
*/
/* If a write could not happen at all */
foreach ($result->getWriteErrors() as $writeError) {
    printf("Operation#%d: %s (%d)\n", $writeError->getIndex(), $writeError->getMessage(), $writeError->getCode());
    echo $writeError->getMessage();
}
                        break;

                case "delete":
                        if(!empty($_POST["message_id"])) {
                                mysql_query("DELETE FROM comment WHERE id=".$_POST["message_id"]);
                        }
                        break;
        }
}
?>

