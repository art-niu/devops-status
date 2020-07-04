<?php
include('config/dbconf.php');

$dbName = 'tcsit';

$todayDate = date("Y-m-d");

$regex = new \MongoDB\BSON\Regex('^'. $todayDate ,'m');

$command = new MongoDB\Driver\Command([
		'aggregate' => 'wasAppStatus',
		'pipeline' => [
				['$match' => ['timestamp' => $regex ]],
				['$sort' => ['cell' => 1, 'node' => 1, 'server' => 1, 'appname' => 1 ]],
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
    echo "<td><b>Timestamp</b></td><td><b>Application Server</b></td><td><b>Application</b></td><td><b>Status</b></td>";
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
      if ($value -> status == "Stopped"){
      	$statusColor = "#FF0000";
      } else {
      	$statusColor = "#005858";      	
      }

      $iMServerName=$value -> cell . "\\" . $value -> node . "\\" . $value -> server;
      
      if ($iMServerName == $mServerName) {
      	$displayServerName="";
      } else {
      	$displayServerName=$iMServerName;
      }
      echo "<tr bgcolor=" . $rbgColor . ">";
      echo "<td>" . $value -> timestamp . "</td><td><a href=" . $value -> adminconsole . ">" . $displayServerName . "</a></td><td>" . $value -> appname . "</td> <td><font color=" . $statusColor . ">" . $value -> status . "</font></td>";

      echo "</tr>";
      $mServerName = $iMServerName;
    }
    echo "</table>";
    echo "</center>";
    
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}

?>
