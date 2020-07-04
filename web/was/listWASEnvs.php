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
                                $(\"#wasEnvListDiv\").load(\"/was/listWASEnvs.php\");
                                $('#message_'+id).fadeOut();
                        break;
                }
        },
        error:function (){}
        });
}
</script>
";

$collectionName = 'tcsit.wasEnvironments';

$todayDate = date("Y-m-d");

$regex = new \MongoDB\BSON\Regex('^' . $todayDate, 'm');

$filter = [ 'envname' => ['$ne' => null ]];

/*
 * $options = [
 * // Only return the following fields in the matching documents
 * 'projection' => [
 * 'envname' => 1,
 * '_id' => 1,
 * ],
 * // Return the documents in descending order of views
 * 'sort' => [
 * 'envname' => -1
 * ],
 * ];
 */
$options = [
    /* Return the documents in descending order of views */
    'sort' => [
        'envname' => 1
    ]
];

$query = new MongoDB\Driver\Query($filter, $options);

$readPreference = new MongoDB\Driver\ReadPreference(MongoDB\Driver\ReadPreference::RP_PRIMARY);

echo "<div id=\"eListwasEnvironments\" >";

try {
    
    $cursor = $manager->executeQuery($collectionName, $query, $readPreference);
    
    $rowIndex = 0;
    echo "<center>";
    echo "<table border=1 >";
    $rbgColor = "#FFFFFF";
    
    echo "<td></td><td><b>Environment Name</b></td><td><b>Product Line</b></td><td><b>Stage</b></td><td><b>Server Name</b></td><td><b>JVM Name</b></td>";
    
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
            . "<button id=\"delete_" . $value->envname . "\" onClick=\"callCrudAction('delete','wasEnvironments','" . $value->_id . "')\"><img src=\"/images/x.svg\" height=\"24\" width=\"24\" /> </button></td>" .
            
         "<td>" . $value->envname . "</td><td>" . $value->prodline . "</td> <td>" . $value->stage . "</td><td>" . $value->srvname . "</td><td>" . $value->jvmname . "</td>";
        
        echo "</tr>";
        
    }
    echo "</table>";
    echo "</center>";
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}
?>
