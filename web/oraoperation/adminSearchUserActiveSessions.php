<?php


$requiredRoles = array("helpdesk", "dba", "infrastructure");

$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}
$vSessionScript = 'verifySession.php';

/** @noinspection PhpIncludeInspection */
require ($vSessionScript);

date_default_timezone_set('EST');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value)
        eval("\$$key = \"$value\";");
} else if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (isset($_GET['db'])) {
        $db = $_GET['db'];
    }
}

session_start();
switch ($db) {
    case "prod":
        $_SESSION['dbString'] = "ITSERVICE";
        break;
    
    case "qa":
        $_SESSION['dbString'] = "QADBOARD";
        break;
    
    default:
        $_SESSION['dbString'] = "DBDEV";
}
session_write_close(); 
echo "
<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery-ui/jquery-ui.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.pager.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.serializejson.js\" type=\"text/javascript\"></script>
";

echo "
<script>
$(document).ready(function () {
	$(\"#userName\").autocomplete({
            dataType: \"json\",
            minLength: 2,
            source: \"/oraoperation/getOraUserName.php\",
            select: function(event,ui) {
                $(\"#userName\").val(ui.item.username);
            }
	});
});

</script>
";

echo "
<script>
$(function(){

        $(document).off('click', \"#checkUserSessions\").on('click', \"#checkUserSessions\", function (e) {
        	e.preventDefault();
	        //$(\"#typeinUserName\").hide();

        	//var pwdData=$(\":input\").serializeArray();
                var userName=$(\"#userName\").val();
                formData = [];
                $.each(['userName'], function(index, value) {
                    formData.push({name: value,value: eval(value) });
                });

        	        $.ajax({
                	type: \"POST\",
	                url: \"/oraoperation/showUserActiveSessions.php\",
        	        dataType : 'html',
                	data: formData,
	                success: function(data, textStatus, jQxhr ){
                        	$(\"#returnDiv\").html(data);
				$(\"#typeinUserName\").show();
        	        },
                	error: function( jqXhr, textStatus, errorThrown ){
				$(\"#typeinUserName\").show();
                        	$(\"#messageDiv\").html(errorThrown);
	                    console.log( errorThrown );
        	        }
                	});
	    });

});

</script>
";

echo "<table border=0 id=\"outLineTable\" class=\"centretable\" width=\"100%\">";

echo "<tbody id=\"typeinUserName\">";

echo "<tr>";
echo "<td align=\"center\">";
echo "<div id=\"envDiv\" class=\"envDiv\">";
echo '<img src="/images/oracle-' . $db . '-sign.svg" alt="' . $db . '" />';
echo "</div>";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td align=\"center\">";

echo "<table border=0 id=\"sessionContentTable\">";

echo "<form id=\"chkDBSessions\" method=\"POST\">";
echo "<tr>";
echo "<td>Oracle User Name: </td> <td><input type=\"text\" id=\"userName\" name=\"userName\" class=\"search\"/>";
echo "</td>";
echo "<td align=\"center\">";
echo "<input type=\"submit\" id=\"checkUserSessions\" name=\"checkUserSessions\" value=\"Check User\"/>";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "</td>";
echo "</tr>";
echo "</form>";
echo "</tbody>";

echo "<tr>";
echo "<td>";
echo "<div id=\"returnDiv\" class=\"returnDiv\">";
echo "<md></md>";
echo "</div>";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td>";
echo "<div id=\"messageDiv\" class=\"mssgDiv\">";
echo "<md></md>";
echo "</div>";
echo "</td>";
echo "</tr>";

echo "</table>";

?>
