<?php
include ('../config/dbconf.php');

$dbName = 'tcsit';

$todayDate = date ( "Y-m-d" );

$regex = new \MongoDB\BSON\Regex ( '^' . $todayDate, 'm' );

$command = new MongoDB\Driver\Command ( [ 
		'aggregate' => 'latestWASStatus',
		'pipeline' => [ 
				[ 
						'$sort' => [ 
								'timestamp' => -1,
								'cell' => 1,
								'node' => 1,
								'server' => 1,
								'appname' => 1 
						] 
				] 
		],
		'cursor' => new stdClass () 
] );

try {
	$cursor = $manager->executeCommand ( $dbName, $command );
	
	$rowIndex = 0;
	echo '<link href="/css/buttons.css" type="text/css" rel="stylesheet" />';
	echo "<table border=1 >";
	$rbgColor = "#FFFFFF";
	echo "<center>";
	// Table Header
	echo "<td></td><td><b>Timestamp</b></td><td><b>Application Server</b></td><td><b>Application</b></td><td><b>Status</b></td><td><b>Application URL</b></td>";
	// Merged Servername cellname\nodename\servername
	$mServerName = "";
	$iMServerName = "";
	foreach ( $cursor as $document => $value ) {
		if ($rowIndex == 1) {
			$rbgColor = "#E5E3E2";
			$rowIndex = 0;
		} else {
			$rbgColor = "#FFFFFF";
			$rowIndex = 1;
		}
		
		$statusColor = "#00FF00";
		if ($value->status == "Stopped") {
			$statusColor = "#FF0000";
		} else {
			$statusColor = "#0E8001";
		}
		
		$serverStatusColor = "#00FF00";
		if ($value->status == "Stopped") {
		    $serverStatusColor = "#FF0000";
		} else {
		    $serverStatusColor = "#0E8001";
		}
		
		$urlColor = "#00FF00";
		if ($value->status == "Invalid") {
			$urlColor = "#FF0000";
		} else {
			$urlColor = "#0E8001";
		}
		
		$iMServerName = $value->cell . "\\" . $value->node . "\\" . $value->server;
		
		if ($iMServerName == $mServerName) {
			$displayServerName = "";
		} else {
			$displayServerName = $iMServerName;
		}
		echo "<tr bgcolor=" . $rbgColor . ">";
		echo "<td><button id=\"btnEditAction_" . $value->_id . "\" name=\"submit\" onClick=\"callCrudAction('edit','" . $value->_id . "')\">Edit</button></td><td>" . 
		  		$value->timestamp . "</td><td><a href=" . $value->adminconsole . ">" . "<font color=" . $serverStatusColor . ">" . $displayServerName . "</font></a></td><td><a href=" . $value->appurl . ">" . $value->appname . 
		"</a></td> <td><font color=" . $statusColor . ">" . $value->status . "</font></td><td><font color=" . $urlColor . ">" . $value->appurlvalidity . "</font></td>";
		
		echo "</tr>";
		$mServerName = $iMServerName;
	}
	echo "</table>";
	echo "</center>";
} catch ( MongoDB\Driver\Exception\Exception $e ) {
	echo $e->getMessage (), "\n";
}

?>
