<?php
// Create connection to Oracle
session_start();
date_default_timezone_set('EST');

$requiredRoles = array("helpdesk", "dba", "infrastructure");

$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value)
        eval("\$$key = \"$value\";");
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['db'])) {
        $db = $_GET['db'];
    }
}

if ( !isset($db) || $db == "" ) {
    $db = $_SESSION['db'];
} else {
    $_SESSION['db'] = $db;
}

switch ($db) {
    case "prod":
        $_SESSION['dbString'] = "ITSERVICE";
        $titleString = "Database: Production";
        break;
        
    case "qa":
        $_SESSION['dbString'] = "QADBOARD";
        $titleString = "Database: QA";
        break;
        
    default:
        $_SESSION['dbString'] = "DBDEV";
        $titleString = "Database: Development";
        
}

echo "<link rel=\"stylesheet\" href=\"../css/tablesorter.css\" type=\"text/css\" media=\"print, projection, screen\" />";
echo "<link rel=\"stylesheet\" href=\"../css/jquery.tablesorter.pager.css\" type=\"text/css\" />";
echo '<link href="/css/buttons.css" type="text/css" rel="stylesheet" />';

echo "

<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery-ui/jquery-ui.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/parser-network.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.pager.js\" type=\"text/javascript\"></script>";

// Verify if the user logged in
$vSessionScript = 'verifySession.php';

require ($vSessionScript);

$commonFunctions = 'oraPasswordFuncs.php';

require ("config.php");
require ($commonFunctions);
require ("sharedFuncs.php");

require ("oraWalletEnv.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value)
        eval("\$$key = \"$value\";");
    // echo $key.'='.$value.'<br />';
}

if (isset($_SESSION['dbString'])) {
    $dbString = $_SESSION['dbString'];
}

if (empty($userName)) {
    if (isset($_SESSION['tgtuid'])) {
        $userName = $_SESSION['tgtuid'];
    } else {
        if (isset($_SESSION['uid'])) {
            $userName = strtoupper($_SESSION['uid']);
        } else {
            echo "Please login to continue.";
            exit();
        }
    }
} else {
    // Save the target uid for non-end user use
    $_SESSION['tgtuid'] = strtoupper($userName);
}

$userName = strtoupper($userName);

echo "
<script>
$(document).ready(function() {
    $('td :checkbox').change(function() {
        $(this).closest('tr').toggleClass('highlight');
    }); 
});
</script>
";

echo "
<script>

$(function(){

        $(document).off('click', \"#selectAll\").on('click', \"#selectAll\", function (e) {
                $(':checkbox').prop('checked', this.checked);
                $(\"#allSessions\").hide();
                $(\"#clearSessions\").show();
        });

        $(document).off('click', \"#clearSessions\").on('click', \"#clearSessions\", function (e) {
                $(':checkbox').prop('checked', this.checked);
                $(\"#allSessions\").show();
                $(\"#clearSessions\").hide();
        });

        $(document).off('click', \"#checkAll\").on('click', \"#checkAll\", function (e) {
	       	$('input:checkbox').not(this).prop('checked', this.checked);
    	});

        $(document).off('click', \"#refreshSessions\").on('click', \"#refreshSessions\", function (e) {
        	e.preventDefault();
                $(\"#userDBSessionsDiv\").load(\"/oraoperation/listOracleDBLocks.php\");
	});

        //Remove attached event handler

        $(document).off('click', \"#killSelectSessions\").on('click', \"#killSelectSessions\", function (e) {
        	e.preventDefault();
	        //$(\"#messageDiv\").hide();

        	var pwdData=$(\":input\").serializeArray();
                var userName=$(\"#userName\").val();

                // Kill Database Sessions
                $.ajax({
                        type: \"POST\",
                        url: \"/oraoperation/killUserSessions.php\",
                        dataType : 'html',
                        data: pwdData,
                        success: function(data, textStatus, jQxhr ){
                            if ($.trim(data) == 'SUCCESS') {
                                $(\"#userDBSessionsDiv\").load(\"/oraoperation/listOracleDBLocks.php\");
                                msg = userName + '\'s selected Oracle DB Sessions have been killed successfully.';
                                $(\"#messageDiv\").show();
                                $(\"#messageDiv\").html(msg);
                            } else {
                                $(\"#userDBSessionsDiv\").load(\"/oraoperation/listOracleDBLocks.php\");
                                $(\"#messageDiv\").html(data);
                            }
                        },
                        error: function( jqXhr, textStatus, errorThrown ){
                                $(\"#messageDiv\").html(errorThrown);
                            console.log( errorThrown );
                        }
                });

	});

});
</script>
";

if (isset($_SESSION['dbString'])) {
    $dbString = $_SESSION['dbString'];
}

$conn = oci_connect("/", "", $dbString, null, OCI_CRED_EXT);

echo "<div id=\"userDBSessionsDiv\" >";

if (! $conn) {
    $m = oci_error();
    
    echo $m['message'] . "\n";
    exit();
} else {
    $sql = 'BEGIN csisadmin.administer_users.get_blocking_sessions(:P_OUT); END;';
    $stmt = oci_parse($conn, $sql);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":P_OUT", $cursor, - 1, OCI_B_CURSOR);
    
    // Application User ID won't have check all feature
    $appIdRexp = "/^csis/i";
    
    $return = oci_execute($stmt);
    if ($return) {
        
        oci_execute($cursor);
        
        echo "<table border=0 id=\"outLineTable\" class=\"centretable\" width=\"100%\">";
        echo "<tr>";
        echo "<td align=\"center\">";
        echo "<div id=\"envDiv\" class=\"envDiv\">";
        echo '<img src="/images/oracle-' . $db . '-sign.svg" alt="' . $db . '" />';
        echo "</div>";
        echo "</td>";
        echo "</tr>";
        
        echo "<tr>";
        echo "<td align=\"center\">";
        
        echo "<form id=\"sessionOperation\">";
        echo "<table border=1 id=\"sessionContentTable\">";
        echo "<thead><tr>";
        echo "<th>Offender txn start@</th><th>Offender SID</th><th>Offenderserialnumber</th><th>Offenderusername</th><th>Offenderprogram</th><th>Offendermodule</th><th>Offendertablesintransaction</th><th>Blockedsid</th><th>Blockedusername</th><th>Blockedprogram</th><th>Blockedmodule</th><th><input type=\"checkbox\" id=\"checkAll\" ></th>";
        echo "</tr></thead>";
        echo "<tfoot><tr>";
        echo "<th>Offender txn start@</th><th>Offender SID</th><th>Offenderserialnumber</th><th>Offenderusername</th><th>Offenderprogram</th><th>Offendermodule</th><th>Offendertablesintransaction</th><th>Blockedsid</th><th>Blockedusername</th><th>Blockedprogram</th><th>Blockedmodule</th><th></th>";
        echo "</tr></tfoot>";
        // echo "<tbody>";
        
        $nosession = true;
        while (($row = oci_fetch_array($cursor, OCI_BOTH)) != false) {
            echo "<tr>";
            echo "<td>" . $row['OFFENDER_TXN_START_TIME'] . "</td>"  . "<td>" . $row['OFFENDER_SID'] . "</td>"  . "<td>" . $row['OFFENDER_SERIAL_NUMBER'] . "</td>"  . "<td>" . $row['OFFENDER_USERNAME'] . "</td>"  . "<td>" . $row['OFFENDER_PROGRAM'] . "</td>"  . "<td>" . $row['OFFENDER_MODULE'] . "</td>"  . "<td>" . $row['OFFENDER_TABLES_IN_TRANSACTION'] . "</td>"  . "<td>" . 
                $row['BLOCKED_SID'] . "</td>"  . "<td>" . $row['BLOCKED_USERNAME'] . "</td>"  . "<td>" . $row['BLOCKED_PROGRAM'] . "</td>"  . "<td>" . $row['BLOCKED_MODULE'] . "</td>" . 
            "<td><input type=\"checkbox\" name=\"session_list[]\" value=\"" . $row['OFFENDER_USERNAME']  . "," . $row['OFFENDER_SID']  . "," . $row['OFFENDER_SERIAL_NUMBER']  . "," . $row['OFFENDER_PROGRAM'] . "\"></td>";
            
            echo "</tr>";
            $nosession = false;
        }
        // echo "</tbody>";
        
        echo "</table>";
        echo "</form>";
        
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=\"center\">";
        if ($nosession) {
            echo "<input type=\"submit\" id=\"killSelectSessions\" name=\"killSelectSessions\" value=\"Kill Selected Sessions\" disabled/>    ";
            echo "<input type=\"submit\" id=\"refreshSessions\" name=\"refreshSessions\" value=\"Refresh Sessions\"/>";
            // echo "<button id=\"refreshSessions\" name=\"refreshSessions\" > Refresh Sessions</button>";
        } else {
            echo "<input type=\"submit\" id=\"killSelectSessions\" name=\"killSelectSessions\" value=\"Kill Selected Sessions\"/>    ";
            echo "<input type=\"submit\" id=\"refreshSessions\" name=\"refreshSessions\" value=\"Refresh Sessions\"/>";
        }
        
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=\"center\">";
        echo "<div id=\"messageDiv\" class=\"mssgDiv\">";
        echo "<md></md>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=\"center\">";
        echo "<div id=\"killIASMessageDiv\" class=\"iasMssgDiv\">";
        echo "<md></md>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
        
        echo "</table>";
    } else {
        echo "Failed to get your sessions, please contact DBA.";
    }
}

echo "</div>";

// Close the Oracle connection
oci_free_statement($stmt);
oci_free_statement($cursor);
oci_close($conn);
?>
