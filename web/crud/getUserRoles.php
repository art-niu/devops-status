<?php
// Create connection to Oracle

session_start();

date_default_timezone_set('EST');
function setUserRoles()
{
    $currentWorkingDir = dirname(dirname(__FILE__));
    $commonDir = $currentWorkingDir . "/common";
    if (realpath($commonDir)) {
        set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir);
    }
    // Verify if the user logged in
    $vSessionScript = 'verifySession.php';
    $mongoDBConf = 'dbconf.php';
    require ("sharedFuncs.php");
    require ($vSessionScript);
    require ($mongoDBConf);

    $tgtCollection = "tcsit.userroles";

    $uid = $_SESSION['uid'];

    $filter = [
        'userid' => $uid
    ];
    $options = [];

    $query = new MongoDB\Driver\Query($filter, $options);

    $readPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);
    $cursor = $manager->executeQuery($tgtCollection, $query, $readPreference);

    if (count($cursor) == "1") {
        $returnArray = array();

        foreach ($cursor as $document) {
            $row_array = (array) $document;
            $docArray['active'] = $row_array['active'];
            $docArray['roles'] = $row_array['roles'];
            array_push($returnArray, $docArray);
        }

        // echo json_encode($docArray['roles']);
        $_SESSION['roles'] = $docArray['roles'];
    } else {
        $_SESSION['roles'] = 'UNKNOWN';
        // echo "Multiple Documents Found.";
    }
}
?>