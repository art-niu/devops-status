<?php
$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir) or realpath($configDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}
require ("sharedFuncs.php");
require ("dbconf.php");

echo "<html>";
echo "<head>";
echo "<link rel=\"stylesheet\" href=\"../css/tablesorter.css\" type=\"text/css\" media=\"print, projection, screen\" />";
echo "<link rel=\"stylesheet\" href=\"../css/jquery.tablesorter.pager.css\" type=\"text/css\" />";

echo "
<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery-ui/jquery-ui.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/parser-network.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.pager.js\" type=\"text/javascript\"></script>

<script>
function copyUpdateFiles(id) {
    /* Get the text field */
    var copyText = document.getElementById(\"uf_\" + id);
        
  /* Select the text field */
  copyText.select();
        
  /* Copy the text inside the text field */
  document.execCommand(\"copy\");
        
  /* Alert the copied text */
  alert(\"Updated File List Copied: \" + copyText.value);
}
        
</script>
";

echo "<script>";
echo '
$(document).ready(function()
  {
    $("#contentTable")
    .tablesorter( {
        widthFixed: true,
        headers: {
            9: {
                sorter: false
            },
            10: {
                sorter: false
            }
        }
    });
  //.tablesorterPager({container: $("#pager")});
});
';
echo "</script>";
echo "</head>";

echo "<body>";

$todayDate = date("Y-m-d");

$collectionName = "dbOffHostEncryptionStatus";
$command = new MongoDB\Driver\Command([
    'aggregate' => $collectionName,
    'pipeline' => [
        [
            '$sort' => [
                'startat' => -1,
                'primaryindex' => 1
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
        echo "$(\"#key-" . $ttvalue->_id . "\").tooltip();\r";
        //echo "$(\"#sc-" . $ttvalue->_id . "\").tooltip();\r";
    }
    echo "         });
      </script>
    ";

    $rowSwithch = 0;
    echo "<table border=1 id=\"contentTable\" class=\"tablesorter\">";
    echo "<thead><tr>";
    echo "<th>Primary Index</th><th>Start @</th><th>Finish @</th><th>Elapsed</th><th>Run @</th><th>Source</th><th>Backup</th><th>Encrypted Destination</th><th>Updated File Number</th><th>Updated File List</th>";
    echo "</tr></thead>";
    echo "<tfoot><tr>";
    echo "<th>Primary Index</th><th>Start @</th><th>Finish @</th><th>Elapsed</th><th>Run @</th><th>Source</th><th>Backup</th><th>Encrypted Destination</th><th>Updated File Number</th><th>Updated File List</th>";
    
//    echo "<th>primaryindex</th><th>startat</th><th>finishat</th><th>elapsedtime</th><th>runon</th><th>sourcebackup</th><th>destinationbackupfolder</th><th>destinationoffhostfolder</th><th>nooffilesupdated</th><th>updatedfiles</th>";
    echo "</tr></tfoot>";

    $rbgColor = "#FFFFFF";
    echo "<center>";

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
        
        $updatedFilessTableString = "<table border=0>";
        $cpText = "";
        
        foreach ($value->updatedfiles as $updatedFile)
        {
            $arrayString = $arrayString . "<tr><td>" . $updatedFile . "</td></tr>";
            $cpText =$updatedFile .  "," . $cpText ;
        }
            if (! $arrayString == "") {
                $updatedFilessTableString = $updatedFilessTableString . $arrayString . "</table>";
            } else {
                $updatedFilessTableString = "";
            }
        
        echo "<tr bgcolor=" . $rbgColor . ">";

        echo  "<td>" . "<a id = \"key-" . $value->_id . " href = \"#\" title = \"" . $value->keyfile . "\">" . $value->primaryindex . "</a>" . "</td>" . "<td>" . $value->startat . "</td>" . "<td>" . $value->finishat . "</td>" .  "<td>"  . gmdate("H:i:s", $value->elapsedtime) .  "</td>" 
                    . "<td>" . $value->runon . "</td>" . "<td>" . $value->sourcebackup . "</td>" . "<td>" . $value->destinationbackupfolder . "</td>" . "<td>" . $value->destinationoffhostfolder . "</td>" 
                        . "<td>" . $value->nooffilesupdated . "</td>" 
                            . "<td><input class='copyfrom' tabindex='-1' aria-hidden='true' id=\"uf_" . $value->_id . "\" value='" . $cpText . "'>" . " <button id=\"clBT_" . $value->_id . "\" onClick=\"copyUpdateFiles('" . $value->_id . "')\"> Copy File List </button>" . "</td>";
                
                
        echo "</tr>";
        $mServerName = $iMServerName;
    }
    echo "</table>";
    echo "</center>";
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}
echo "</body>";
echo "</html>";

?>
