<?php
session_start(); 
if(isset($_SESSION['fullname'])) 
   $fullName = $_SESSION['fullname']; // holds url for last page visited.
else 
   header("Location: https://dboard.csd.toronto.ca/login.php");

echo '
<table style="width:100%" class="outLineTable">
<tr>
<th>WebSphere Applications</th>
<th>Oracle Database Instances</th>
</tr>';

echo '<tr>
<td valign="top" id="leftDashBoard" ><div id="listWASStatusToday">';
echo "<center>";
include 'crud/latestWASStatus.php';
echo "</center>";
echo '</div></td>';

echo '<td valign="top" id="middleDashBorard">';
echo '<div id="listOraInstStatusToday">';
echo "<center>";

echo '<H3>V9PROD: </H3>';

$_GET['db'] = 'prod';
include 'oraoperation/eGetOraDatabaseStatus.php';

echo '--------------------<br>';
echo '<H3>CSISQA: </H3>';

$_GET['db'] = 'qa';
include 'oraoperation/eGetOraDatabaseStatus.php';

echo "</center>";
echo '</div>';

echo '<div id="listRAC1StatusToday">';
echo "<center>";
echo "";
echo '<H3>Below RAC 1 Status is updated every day.</H3>';
echo "";

include 'crud/latestRAC1Status.php';
echo "</center>";
echo '</div>';

echo '<div id="listURLStatus">';
echo "<center>";
echo "";
echo '<H3>Monitored URLs</H3>';
echo "";

include 'applications/listUrlStatus.php';
echo "</center>";
echo '</div>';

echo '</td>';

echo '</tr>
</table>
';

?>

