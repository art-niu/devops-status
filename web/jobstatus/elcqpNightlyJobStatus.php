<?php

/*
 * session_start();
 * if(isset($_SESSION['fullname']))
 * $fullName = $_SESSION['fullname']; // holds url for last page visited.
 * else
 * header("Location: https://dboard.csd.toronto.ca/login.php");
 */
$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir) or realpath($configDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}
require ("sharedFuncs.php");
require ("dbconf.php");

$todayDate = date("Y-m-d");

$regex = new \MongoDB\BSON\Regex('^' . $todayDate, 'm');

$command = new MongoDB\Driver\Command([
    'aggregate' => 'elcqpNightlyJobs',
    'pipeline' => [
        [
            '$match' => [
                'startat' => [
                    '$gte' => new MongoDB\BSON\UTCDateTime(strtotime(date('Y-m-d', strtotime('-7 days'))) * 1000)
                ]
            ]
        ],
        [
            '$sort' => [
                'jobId' => - 1
            ]
        ]
    ],
    'cursor' => new stdClass()
]);

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
function showEditBox(editobj,id) {
        $(editobj).prop('readonly','');
        $(\"#urlContent_\"+ id ).show();
        var currentUrl = $(\"#urlContent_\"+ id ).html();
        var editMarkUp = '<button name=\"ok\" onClick=\"callCrudAction(\'edit\',\'' + id + '\')\">Save</button><button name=\"cancel\" onClick=\"cancelEdit(\'' + id + '\')\">Cancel</button>';
        $(\"#urlContent_\"+ id ).html(editMarkUp);
}

function cancelEdit(id) {
        $(\"#urlContent_\"+ id ).hide();
        $(\"#urlText_\"+ id ).prop('readonly','true');
        //$(\"#urlEdit_\"+id).show();
        //$(\"#btnEditAction_\"+id).prop('disabled','');
}

function callCrudAction(action,id) {
        //$(\"#loaderIcon\").show();
        $(\"#urlContent_\"+ id ).hide();
        $(\"#urlText_\"+ id ).prop('readonly','true');
        var queryString;
        switch(action) {
                case \"add\":
                        queryString = 'action='+action+'&txtmessage='+ $(\"#txtmessage\").val();
                break;
                case \"edit\":
                        queryString = 'action='+action+'&id='+ id + '&appurl='+ $(\"#urlText_\"+id).val();
                break;
                case \"delete\":
                        queryString = 'action='+action+'&message_id='+ id;
                break;
        }
        jQuery.ajax({
        url: \"/crud/wasDeploymentStatusCrud.php\",
        data:queryString,
        type: \"POST\",
        success:function(data){
                switch(action) {
                        case \"add\":
                                $(\"#comment-list-box\").append(data);
                        break;
                        case \"edit\":
                          $(\"#urlContent_\"+ id ).hide();
                          $(\"#urlText_\"+ id ).prop('readonly','true');
                        break;
                        case \"delete\":
                                $('#message_'+id).fadeOut();
                        break;
                }
                //$(\"#txtmessage\").val('');
                //$(\"#loaderIcon\").hide();
        },
        error:function (){}
        });
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
            7: {
                sorter: false
            },
            9: {
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

try {
    $cursor = $manager->executeCommand($dbName, $command);
    
    echo "
      <script>
       $(function() {";
    
    foreach ($cursor as $document => $ttvalue) {
        echo "$(\"#msg-" . $ttvalue->_id . "\").tooltip();\r";
        echo "$(\"#retry-" . $ttvalue->_id . "\").tooltip();\r";
    }
    echo "         });
      </script>
";
    $rowIndex = 0;
    echo '<link href="/css/buttons.css" type="text/css" rel="stylesheet" />';
    echo "<center>";
    $rbgColor = "#FFFFFF";
    echo "<table border=1 id=\"contentTable\" class=\"tablesorter\">";
    // Table Header
    echo "<thead><tr>";
    echo "<th>Job ID</th><th>Running On</th><th>Started @</th><th>Finished @</th><th>Day</th><th>Retries</th><th>Transfer</th><th>Verification</th><th>Backup</th><th>a2z</th><th>elcaqi</th><th>webreg</th>";
    echo "</tr></thead>";
    echo "<tfoot><tr>";
    echo "<th>Job ID</th><th>Running On</th><th>Started @</th><th>Finished @</th><th>Day</th><th>Retries</th><th>Transfer</th><th>Verification</th><th>Backup</th><th>a2z</th><th>elcaqi</th><th>webreg</th>";
    echo "</tr></tfoot>";
    echo "<tbody>";
    
    $cursor = $manager->executeCommand($dbName, $command);
    foreach ($cursor as $document => $value) {
        if ($rowIndex == 0) {
            $rbgColor = "#CCEEFF";
            $rowIndex = 1;
        } else {
            $rbgColor = "#FFFFFF";
            $rowIndex = 0;
        }
        
        $statusColor = "#00FF00";
        $urlColor = "#00FF00";
        
        $startAtLocal = convertMongoTime2LocalTime($value->startat);
        $finishAtLocal = convertMongoTime2LocalTime($value->finishat);
        $weekDay = date("l", strtotime($finishAtLocal)); 
        echo "<tr bgcolor=" . $rbgColor . ">";
        echo "<td>" . "<a id = \"msg-" . $value->_id . "href = \"#\" title = \"" . $value->errormessage . "\">" . $value->jobId . "</a>" . "</td><td>" . $value->runningon . "</td><td>" . $startAtLocal . "</td> <td>" . $finishAtLocal . "</td> <td> ". $weekDay . "</td><td>" . "<a id = \"retry-" . $value->_id . "href = \"#\" title = \"" . $value->retrymsg . "\">" . $value->retries . "</a>" . "</td><td>" . $value->transfer . "</td><td>" . $value->verification . "</td><td>" . $value->backup . "</td><td>" . "<a id = \"a2z-" . $value->_id . "href = \"#\" title = \"" . "Generated @: " . $value->a2zFileTStamp . "\">" . $value->a2zNewFiles . "</a>" . "</td><td>" . "<a id = \"elcaqi-" . $value->_id . "href = \"#\" title = \"" . "Generated @: " . $value->elcaqiFileTStamp . "\">" . $value->elcaqiNewFiles . "</a>" . "</td><td>" . "<a id = \"webreg-" . $value->_id . "href = \"#\" title = \"" . "Generated @: " . $value->webregFileTStamp . "\">" . $value->webregNewFiles . "</a>" . "</td>";
        
        echo "</tr>";
    }
    echo "</tbody>";
    
    echo "</table>";
    echo "";
    echo "</center>";
    /*
     * echo "<div id=\"pager\" class=\"pager\">
     * <form>
     * <img src=\"../themes/first.png\" class=\"first\"/>
     * <img src=\"../themes/prev.png\" class=\"prev\"/>
     * <input type=\"text\" class=\"pagedisplay\"/>
     * <img src=\"../themes/next.png\" class=\"next\"/>
     * <img src=\"../themes/last.png\" class=\"last\"/>
     * <select class=\"pagesize\">
     * <option selected=\"selected\" value=\"10\">10</option>
     * <option value=\"20\">20</option>
     * <option value=\"30\">30</option>
     * <option value=\"40\">40</option>
     * </select>
     * </form>
     * </div>";
     */
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}

echo "</body>";
echo "</html>";

?>
