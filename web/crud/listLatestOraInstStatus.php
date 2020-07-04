<?php
$dbConfFile='../config/dbconf.php';

date_default_timezone_set('EST');

if (!file_exists($dbConfFile)) {
    $dbConfFile='config/dbconf.php';
}

include_once($dbConfFile);

$dbName = 'tcsit';

$todayDate = date("Y-m-d");

$regex = new \MongoDB\BSON\Regex('^'. $todayDate ,'m');

$command = new MongoDB\Driver\Command([
		'aggregate' => 'latestOraStatus',
		'pipeline' => [
				['$match' => ['timestamp' => $regex, 'host' => ['$ne' => "", '$exists' => true ] ]],
				['$sort' => ['priority' => 1,'timestamp' => -1, 'cell' => 1, 'node' => 1, 'server' => 1, 'appname' => 1 ]],
		],
		'cursor' => new stdClass,
]);

try {
    $cursor = $manager->executeCommand($dbName, $command);
    
    $rowIndex = 0;
    echo "<table border=1 >";
    $rbgColor="#FFFFFF";
    echo "<center>";
    //Table Header
    echo "<td><b>Service Name</b></td><td><b>Status</b></td><td><b>Host</b></td><td><b>Port</b></td><td><b>Timestamp</b></td>";
    //Merged Servername cellname\nodename\servername
    $mServerName="";    
    $iMServerName="";    
    foreach ($cursor as $document => $value) {
      if ( $rowIndex == 0) {
      	$rbgColor="#CCEEFF";
        $rowIndex = 1;
      } else {
      	$rbgColor="#FFFFFF";
        $rowIndex = 0;
      }
      
      $statusColor = "#00FF00";
      if ($value -> status == "Down" || $value -> status == "Invalid"){
      	$statusColor = "#FF0000";
        $appStatus = '<img src="/images/arrow-down-red.jpg" alt="' . $value->status . '" height="24" width="24">';
      } else {
      	$statusColor = "#0E8001";      	
        $appStatus = '<img src="/images/arrow-up-green.jpg" alt="' . $value->status . '" height="24" width="24">';

      }

      $iMServerName=$value -> host;
      
      if ($iMServerName == $mServerName) {
      	$displayServerName="";
      } else {
      	$displayServerName=$iMServerName;
      }
      echo "<tr bgcolor=" . $rbgColor . ">";
      echo "<td>" . $value -> instance
      . "</td> <td>" . $appStatus . "</td><td>" . $displayServerName . "</td><td>" . $value -> port  
      . "</td><td>" . $value -> timestamp . "</td>";

      echo "</tr>";
      $mServerName = $iMServerName;
    }
    echo "</table>";
    echo "</center>";
    
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}

?>
