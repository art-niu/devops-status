<?php
// Create connection to Oracle
session_start();
date_default_timezone_set('EST');

$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir);
}
// Verify if the user logged in
$vSessionScript = 'verifySession.php';
$mongoDBConf = 'dbconf.php';
require_once ("sharedFuncs.php");
require_once ($vSessionScript);
require_once ($mongoDBConf);

echo "<link rel=\"stylesheet\" href=\"../css/tablesorter.css\" type=\"text/css\" media=\"print, projection, screen\" />";
echo "<link rel=\"stylesheet\" href=\"../css/jquery.tablesorter.pager.css\" type=\"text/css\" />";
echo '<link href="/css/buttons.css" type="text/css" rel="stylesheet" />';

echo "
    
<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery-ui/jquery-ui.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/parser-network.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.pager.js\" type=\"text/javascript\"></script>
    
<script>
function showEditBox(editobj,id, fieldName, collectionName) {
		$(editobj).prop('readonly','');
        $(\"#b\"+ id ).show();
        var currentValue = $(id).html();
        var editMarkUp = '<button name=\"ok\" onClick=\"callCrudAction(\'edit\',\'' + collectionName + '\',\'' + id + '\')\">Save</button><button name=\"cancel\" onClick=\"cancelEdit(\'' + id + '\')\">Cancel</button>';
        $(\"#b\"+ id ).html(editMarkUp);
    
    
}
    
function cancelEdit(id) {
        $(\"#b\"+ id ).hide();
        $(\"#\"+ id ).prop('readonly','true');
}
    
    
function callCrudAction(action,collectionName,id,fieldName) {
        $(\"#b\"+ id ).hide();
        $(\"#\"+ id ).prop('readonly','true');
        var queryString;
        switch(action) {
                case \"add\":
                        queryString = 'action='+action+'&txtmessage='+ $(\"#txtmessage\").val();
                break;
                case \"edit\":
                        queryString = 'action='+action+'&id='+ id + '&collection=' + collectionName + '&field=' + fieldName + '&value='+ $(\"#\"+ id ).val();
                break;
                case \"delete\":
                        queryString = 'action='+action+'&id='+ id + '&collection=' + collectionName;
                break;
        }
        jQuery.ajax({
        url: \"/crud/generalCRUD.php\",
        data:queryString,
        type: \"POST\",
        success:function(data){
                switch(action) {
                        case \"add\":
                                $(\"#comment-list-box\").append(data);
                        break;
                        case \"edit\":
                          $(\"#b\"+ id ).hide();
                          $(\"#\"+ id ).prop('readonly','true');
                        break;
                        case \"delete\":
                                $(\"#eList\" + collectionName).load(\"/was/elistDeployments.php\");
                                $('#message_'+id).fadeOut();
                        break;
                }
        },
        error:function (){}
        });
}
</script>
";

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
echo "<div id=\"deploymentListDiv\" >";
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
            . "<button id=\"delete_" . $value->deploymentname . "\" onClick=\"callCrudAction('delete','wasDeployments','" . $value->_id . "')\"><img src=\"/images/x.svg\" height=\"24\" width=\"24\" /> </button></td>" 
            . "<td>" . $value->deploymentname . "</td><td>" . $value->appname . "</td>" 
            . "<td><div id=\"pWASDEPLOYMENT_" . $value->_id . "\"> <input id=\"WASDEPLOYMENT_" . $value->_id . "\" type=\"text\" size=\"40\" value=\"" . $value->binary . "\" readonly=\"true\" ondblclick=\"showEditBox(this,'WASDEPLOYMENT_" . $value->_id . "','binary','wasDeployments')\"> </div>" . "<div id=\"bWASDEPLOYMENT_" . $value->_id . "\"></div></td>". "<td><button onclick=\"deployApplication('" . $value->envname . " "  
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
