<?php
//session_start();

$currentWorkingDir = dirname(dirname(__FILE__));

$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}

// Verify if the user logged in
$vSessionScript = 'verifySession.php';
$mongoDBConf = 'dbconf.php';
// require_once ("sharedFuncs.php");
// require_once ($vSessionScript);
require_once ($mongoDBConf);

require ($vSessionScript);

if (isset($_SESSION['fullname']))
    $fullName = $_SESSION['fullname'];

if (isset($_SESSION['mail']))
    $userMail = $_SESSION['mail'];

$epocStart = time();
$consoleLog = "runElcqpUpdates_" . $epocStart;
$logFile = "/logs/" . $consoleLog;


echo "<link rel=\"stylesheet\" href=\"../css/tablesorter.css\" type=\"text/css\" media=\"print, projection, screen\" />";
echo "<link rel=\"stylesheet\" href=\"../css/jquery.tablesorter.pager.css\" type=\"text/css\" />";
echo '<link href="/css/buttons.css" type="text/css" rel="stylesheet" />';
//echo '<link href="/js-logtail-general/logtail.css" rel="stylesheet" type="text/css">';


/*
echo "
   <style>
   table td {border:solid 1px #fab; width:300px; word-wrap:break-word;}
   </style>
";
*/

echo "
<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.serializejson.js\" type=\"text/javascript\"></script>
<script src=\"/jquery-ui/jquery-ui.js\" type=\"text/javascript\"></script>
";
//        echo '<script type="text/javascript" src="/js-logtail-general/logtail.js"></script>';

echo "
<script>
            
$(function(){
            
      $(document).off('click', \"#runTask\").on('click', \"#runTask\", function (e) {            
        e.preventDefault();
        $(\"#buttonsSubmitDiv\").hide();
        $(\"#buttonsResetDiv\").hide();
        $(\"#jobProgress\").show();
        $(\"#jobProgress\").load(\"/common/progress.php?desc=ELCQP%20RUNNING\");
        var wasenv = $('input[name=wasEnv]').val();
        var logfile = '"  . $logFile . "_' + wasenv;
        var consolelog = '"  . $consoleLog . "_' + wasenv;
        $(\"#responseDiv\").load(\"/common/tailLog.php?logfile=\" + logfile);
";

echo "
        formData = [];
        $.each(['wasenv', 'consolelog'], function(index, value) {
                    formData.push({name: value,value: eval(value) });
                });
            
        $.ajax({
                type: \"POST\",
                url: \"/was/runElcqpUpdates.php\",
	        dataType : 'html',
                data: formData,
                success: function(data, textStatus, jQxhr ){
                    $(\"#jobProgress\").html(\"Job Finished\");
                    $(\"#responseDiv\").html(data);
    			    if ($.trim(data) == 'success') {
                        	msg = 'ELCQP updated successfully.';
        			$(\"#responseDiv\").html(msg);
			        //$(\"#resetinputs\" ).trigger(\"click\" );
			        } 
		        },
		error: function( jqXhr, textStatus, errorThrown ){runTask
                    $(\"#jobProgress\").hide();
		    $(\"#responseDiv\").html(errorThrown);
              	    //console.log( errorThrown );
                }
        });

        //$.ajax({
            //$(\"#logDiv\").load(\"/common/tailLog.php?logfile=\" + logfile  );
        //});

    });
});
            
</script>
";

if (empty($fullName)) {
    echo "Seems you haven't logged in, please contact helpdesk if you need help.";
}

echo "<div id=\"chooseElcqp\">";
//echo "<table border=0 id=\"outlineTable\" class=\"outLineTable\">";
//echo "<tr>";
//echo "<td align=\"center\">";

echo "<table border=0 id=\"embededLoginTable\" class=\"outLineTable\">";
echo "<tr>";
echo "<tbody id=\"wasEnvDiv\">";
echo "<td align=\"center\">";
echo "<form id=\"chooseELCQPEnv\" method=\"post\" class=\"ora_form\">";
echo "<table border=0 class=\"centreTable\">";

echo "<tr>";
echo "<td><userLable>Application Name: </userLable></td><td>ELCQP</td>";
echo "</tr>";

echo "<tr>";
echo "<td><userLable>WebSphere Environment: </userLable></td>";

echo '<td>
<input type="radio" name="wasEnv" id="wasEnvQA" value="qa" checked>QA<br>
<input type="radio" name="wasEnv" id="wasEnvProd" value="prod">Production<br>
';

echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td><userLable>Local Repository: </userLable></td><td>/data8/SERVPLAN/qawp/qa/data/children/dmc/</td>";
echo "</tr>";

echo "<tr>";
echo "<td><userLable>URL: </userLable></td><td><a href=\"https://secureqa.csd.toronto.ca/data/children/dmc/a2z/a2za.html\"> A-Z list of Licensed Child Care Centres </a></td>";
echo "</tr>";


echo "<tr>";
echo "<td align=\"right\">";
echo "<div id=\"buttonsSubmitDiv\">";
echo "<input type=\"submit\" name=\"submit\" value=\"Submit\" id=\"runTask\" /> ";
echo "</div>";
echo "</td> <td align=\"left\">";
echo "<div id=\"buttonsResetDiv\">";
echo "<input type=\"reset\" name=\"reset\" value=\"Reset\" id=\"resetinputs\" />";
echo "</div>";
echo "</td>";
echo "</tr>";

echo "</table>";

echo "</form>";
echo "</td>";
echo "</tbody>";

echo "</tr>";
echo "</table>";
//echo "</td>";
//echo "</tr>";
//echo "</table>";
echo "</div>";

echo "<table style=\"table-layout:fixed; width:250px\">";
echo "<tr>";
echo "<td align=\"center\">";
echo "<div id=\"jobProgress\" class=\"jobProgress\">";
echo "</div>";

echo "</td>";
echo "</tr>";
echo "</table>";

?>
 
