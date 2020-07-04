<?php
include ('config/dbconf.php');
// $manager = new MongoDB\Driver\Manager("mongodb://localhost:27017");

$query = new MongoDB\Driver\Query([]);

try {
	$cursor = $manager->executeQuery("tcsit.devices", $query);

	echo "<table border=1 >";
	foreach ($cursor as $document => $value) {
		echo "<tr>";
		echo "<td>" . $value->_id . "</td><td>" . $value->ip . "</td><td>" . $value->hostname . "</td>";
		echo "</tr>";
	}
	echo "</table>";
} catch (MongoDB\Driver\Exception\Exception $e) {
	echo $e->getMessage(), "\n";
}

//
