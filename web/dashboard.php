<?php
session_start(); 
if(isset($_SESSION['fullname'])) 
   $fullName = $_SESSION['fullname']; // holds url for last page visited.
else 
   header("Location: https://dboard.csd.toronto.ca/login.php");

require 'config/dbconf.php';

               echo "<table border=0 id=\"outLineTable\" class=\"centretable\" width=\"100%\">";
                echo "<tbody id=\"typeinUserName\">";
                echo "<tr>";
                echo "<td align=\"center\">";

echo '
<table border="0">
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
include 'crud/listLatestOraInstStatus.php';
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

