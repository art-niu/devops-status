<?php
include('config/dbconf.php');

$dbName = 'tcsit';

//"$match": { "timestamp": { "$regex": Value_match } }

$command = new MongoDB\Driver\Command([
		'aggregate' => 'wasAppStatus',
		'pipeline' => [
				['$match' => ['server' => 'seiServer']],
		],
		'cursor' => new stdClass,
]);

try {
    $cursor = $manager->executeCommand($dbName, $command);

    echo "<table border=1 >";
    foreach ($cursor as $document => $value) {
      echo "<tr>";
      echo "<td>" . $value -> _id . "</td><td>" . $value -> server . "</td><td>" . $value -> status . "</td>";
      echo "</tr>";
    }
    echo "</table>";

} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}

?>
