<?php
/*
session_start(); 
if(isset($_SESSION['fullname'])) 
   $fullName = $_SESSION['fullname']; // holds url for last page visited.
else 
   header("Location: http://dboard.csd.toronto.ca/login.php");
*/
$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir) or realpath($configDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir . PATH_SEPARATOR . $currentWorkingDir);
}
require ("dbconf.php");


echo ' <!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="refresh" content="30" />
<title>Toronto Children\'s Services IT Dash Board</title>
				
</head>

<body>
<center>
<h1>
Toronto Children\'s Services IT Dash Board
</h1>
</center>';

echo '
<table style="width:100%" border="1">
<tr>
<th>WebSphere Applications</th>
<th>Oracle Database Instances</th>
</tr>';

echo '<tr>
<td valign="top" id="leftDashBoard" ><div id="listWASStatusToday">';
echo "<center>";
include $_SERVER['DOCUMENT_ROOT'] . '/crud/latestWASStatus.php';
echo "</center>";
echo '</div></td>';

echo '<td valign="top" id="middleDashBorard">';
echo '<div id="listOraInstStatusToday">';
echo "<center>";
include  $_SERVER['DOCUMENT_ROOT'] . '/crud/listLatestOraInstStatus.php';
echo "</center>";
echo '</div>';

echo '<div id="listRAC1StatusToday">';
echo "<center>";
echo "";
echo '<H3>Below RAC 1 Status is updated every day.</H3>';
echo "";

include  $_SERVER['DOCUMENT_ROOT'] . '/crud/latestRAC1Status.php';
echo "</center>";
echo '</div>';
echo '<div id="listURLStatus">';
echo "<center>";
echo "";
echo '<H3>Monitored URLs</H3>';
echo "";

include  $_SERVER['DOCUMENT_ROOT'] . '/applications/listUrlStatus.php';
echo "</center>";
echo '</div>';

echo '</td>';

echo '</tr>
</table>
';

echo '</body>

</html> 
';
?>

