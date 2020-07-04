<?php
//include('../config/dbconf.php');

date_default_timezone_set('EST');

$dbName = 'tcsit';

$todayDate = date("Y-m-d");

$regex = new \MongoDB\BSON\Regex('^'. $todayDate ,'m');

$command = new MongoDB\Driver\Command([
		'aggregate' => 'latestRAC1Status',
		'pipeline' => [
				['$match' => ['timestamp' => $regex, 'dbname' => ['$ne' => "", '$exists' => true ] ]]
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
    echo "<td><b>DB Name</b></td><td><b>Instance</b></td><td><b>Status</b></td><td><b>Node</b></td><td><b>Timestamp</b></td>";
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
      
      if ($value -> node == "sun01"){
      	$rbgColor="#FF0000";
      }
      
      $statusColor = "#00FF00";
      if ($value -> status == "Up"){
      	$statusColor = "#0E8001";      	
        $appStatus = '<img src="/images/arrow-up-green.jpg" alt="' . $value->status . '" height="24" width="24">';
      } else {
      	$statusColor = "#FF0000";
        $appStatus = '<img src="/images/arrow-down-red.jpg" alt="' . $value->status . '" height="24" width="24">';

      }

      echo "<tr bgcolor=" . $rbgColor . ">";
      echo "<td>" . $value -> dbname . "</td><td>" . $value -> instance
      . "</td> <td>" . $appStatus . "</td><td>" . $value -> node  
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
