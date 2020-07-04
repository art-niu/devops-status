<?php
include('config/dbconf.php');

$dbName = 'tcsit';

$todayDate = date("Y-m-d");

$regex = new \MongoDB\BSON\Regex('^'. $todayDate ,'m');

$command = new MongoDB\Driver\Command([
		'aggregate' => 'oraInstanceStatus',
		'pipeline' => [
				['$match' => ['timestamp' => $regex, 'host' => ['$ne' => "", '$exists' => true ] ]],
				['$sort' => ['timestamp' => -1, 'cell' => 1, 'node' => 1, 'server' => 1, 'appname' => 1 ]],
		],
		'cursor' => new stdClass,
]);

try {
    $cursor = $manager->executeCommand($dbName, $command);
    
    $rowSwithch = 0;
    echo "<table border=1 >";
    $rbgColor="#FFFFFF";
    echo "<center>";
    //Table Header
    echo "<td><b>Timestamp</b></td><td><b>Host</b></td><td><b>Port</b></td><td><b>Instance</b></td><td><b>Status</b></td>";
    //Merged Servername cellname\nodename\servername
    $mServerName="";    
    $iMServerName="";    
    foreach ($cursor as $document => $value) {
      if ( $rowIndex == 1) {
      	$rbgColor="#E5E3E2";
        $rowIndex = 0;
      } else {
      	$rbgColor="#FFFFFF";
        $rowIndex = 1;
      }
      
      $statusColor = "#00FF00";
      if ($value -> status == "Down" || $value -> status == "Invalid"){
      	$statusColor = "#FF0000";
      } else {
      	$statusColor = "#005858";      	
      }

      $iMServerName=$value -> host;
      
      if ($iMServerName == $mServerName) {
      	$displayServerName="";
      } else {
      	$displayServerName=$iMServerName;
      }
      echo "<tr bgcolor=" . $rbgColor . ">";
      echo "<td>" . $value -> timestamp . "</td><td>" . $displayServerName . "</a></td><td>" . $value -> port  
      . "</a></td><td>" . $value -> instance
      . "</td> <td><font color=" . $statusColor . ">" . $value -> status . "</font></td>";

      echo "</tr>";
      $mServerName = $iMServerName;
    }
    echo "</table>";
    echo "</center>";
    
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}


