<?php
$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir) or realpath($configDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}
require ("sharedFuncs.php");
require ("dbconf.php");

$todayDate = date("Y-m-d");

$httpResponseCodeCollection = "sys_httpresponsecode";

$regex = new \MongoDB\BSON\Regex('^' . $todayDate, 'm');

$command = new MongoDB\Driver\Command([
    'aggregate' => $httpResponseCodeCollection,
    'pipeline' => [
        [
            '$project' => [
                'httpcode' => 1,
                'description' => 1
            ]
        ],
        [
            '$sort' => [
                'httpcode' => 1,
                'description' => 1
            ]
        ]
    ],
    'cursor' => new stdClass()
]);

$httpCodeArray = array();
try {
    $cursor = $manager->executeCommand($dbName, $command);

    foreach ($cursor as $document => $value) {
        $httpCodeArray[$value->httpcode] = $value->description;
    }
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}

$urlStatusCollection = "urlStatus";
$command = new MongoDB\Driver\Command([
    'aggregate' => $urlStatusCollection,
    'pipeline' => [
        [
            '$sort' => [
                'primaryindex' => 1,
                'startat' => - 1
            ]
        ]
    ],
    'cursor' => new stdClass()
]);
try {
    $cursor = $manager->executeCommand($dbName, $command);

    echo "
      <script>
         $(function() {";

    foreach ($cursor as $document => $ttvalue) {
        echo "$(\"#tt-" . $ttvalue->_id . "\").tooltip();\r";
        echo "$(\"#sc-" . $ttvalue->_id . "\").tooltip();\r";
    }
    echo "         });
      </script>
    ";

    $rowSwithch = 0;
    echo "<table border=1 id=\"urlStatusContentTable\">";
    echo "<thead><tr>";
    echo "<th>URL</th><th>Status</th><th>Checked On</th><th>Checked @</th>";
    echo "</tr></thead>";
    echo "<tfoot><tr>";
    echo "<th>URL</th><th>Status</th><th>Checked On</th><th>Checked @</th>";
    echo "</tr></tfoot>";

    $rbgColor = "#FFFFFF";
    echo "<center>";
    // Table Header
    // Merged Servername cellname\nodename\servername
    $mServerName = "";
    $iMServerName = "";
    $cursor = $manager->executeCommand($dbName, $command);
    foreach ($cursor as $document => $value) {
        if ($rowIndex == 1) {
            $rbgColor="#CCEEFF";
            $rowIndex = 0;
        } else {
            $rbgColor = "#FFFFFF";
            $rowIndex = 1;
        }

        $statusColor = "#00FF00";
        // if ($value -> status == 200 || $value -> status == "Invalid"){
        $goodCode = array(200,302);
        if (in_array($value->status,  $goodCode)) {
            $statusColor = "#0E8001";
            $appStatus = "<a id = \"sc-" . $value->_id . "href = \"#\" title = \"" .  $value->status . " " . $httpCodeArray[$value->status] . "\">" . '<img src="/images/arrow-up-green.jpg" alt="' . $value->status . '" height="24" width="24">' . "</a>";
        } else {
            $statusColor = "#FF0000";
            $appStatus = "<a id = \"sc-" . $value->_id . "href = \"#\" title = \"" . $value->status . " " . $httpCodeArray[$value->status] . "\">" . '<img src="/images/arrow-down-red.jpg" alt="' . $value->status . '" height="24" width="24">' . "</a>";
        }

      
        echo "<tr bgcolor=" . $rbgColor . ">";
        echo "<td>" . "<a id = \"tt-" . $value->_id . "href = \"#\" title = \"" . $value->urldescription . "\">" . $value->url . "</a>" . "</td>"
            . "<td>" . $appStatus . "</td>"
                . "<td>" . $value->updatefrom . "</a></td><td>" . $value->startat . "</a></td>";
                
                
//         echo "<td>" . "<a id = \"tt-" . $value->_id . "href = \"#\" title = \"" . $value->urldescription . "\">" . $value->url . "</a>" . "</td>" 
//             . "<td>" . "<a id = \"sc-" . $value->_id . "href = \"#\" title = \"" .  $httpCodeArray[$value->status] . "\">" . "<font color=" . $statusColor . ">" . $value->status . "</font></a></td>" 
//                 . "<td>" . $value->updatefrom . "</a></td><td>" . $value->startat . "</a></td>";

        echo "</tr>";
        $mServerName = $iMServerName;
    }
    echo "</table>";
    echo "</center>";
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}

?>
