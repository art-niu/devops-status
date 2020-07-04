<?php
$action = isset ( $_POST ['action'] ) ? $_POST ['action'] : '';
$id = $_POST ["id"];
$os = $_POST ["os"];

$dbConfFile = '../config/dbconf.php';

if (! file_exists ( $dbConfFile )) {
	$dbConfFile = 'config/dbconf.php';
}

include_once ($dbConfFile);
$collection = "tcsit.devices";

// printf("Matched %d document(s)\n", $updateResult->getMatchedCount());
// printf("Modified %d document(s)\n", $updateResult->getModifiedCount());

if (! empty ( $action )) {
	switch ($action) {
		case "add" :
			$result = mysql_query ( "INSERT INTO comment(message) VALUES('" . $_POST ["txtmessage"] . "')" );
			if ($result) {
				$insert_id = mysql_insert_id ();
				echo '<div class="message-box"  id="message_' . $insert_id . '">
                                                <div>
                                                <button class="btnEditAction" name="edit" onClick="showEditBox(this,' . $insert_id . ')">Edit</button>
<button class="btnDeleteAction" name="delete" onClick="callCrudAction(\'delete\',' . $insert_id . ')">Delete</button>
                                                </div>
                                                <div class="message-content">' . $_POST ["txtmessage"] . '</div></div>';
			}
			break;
		
		case "edit" :
			$bulk = new MongoDB\Driver\BulkWrite ();
			$mId = new MongoDB\BSON\ObjectId ( $id );
			error_log(print_r($mId, TRUE));
			$bulk->update ( [ 
					'_id' => $mId 
			], [ 
					'$set' => [ 
							'os' => $os 
					] 
			], [ 
					'multi' => false,
					'upsert' => false 
			] );
			
			// $manager = new MongoDB\Driver\Manager('mongodb://localhost:27017');
			
			// $writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 100);
			$writeConcern = new MongoDB\Driver\WriteConcern ( 0, 10000 );
			$result = $manager->executeBulkWrite ( $collection, $bulk, $writeConcern );
			if ($result) {
				echo $os;
			}
			/*
			 * printf("Inserted %d document(s)\n", $result->getInsertedCount());
			 * printf("Matched %d document(s)\n", $result->getMatchedCount());
			 * printf("Updated %d document(s)\n", $result->getModifiedCount());
			 * printf("Upserted %d document(s)\n", $result->getUpsertedCount());
			 * printf("Deleted %d document(s)\n", $result->getDeletedCount());
			 */
			/* If a write could not happen at all */
			foreach ( $result->getWriteErrors () as $writeError ) {
				printf ( "Operation#%d: %s (%d)\n", $writeError->getIndex (), $writeError->getMessage (), $writeError->getCode () );
				echo $writeError->getMessage ();
			}
			break;
		
		case "delete" :
			if (! empty ( $_POST ["message_id"] )) {
				mysql_query ( "DELETE FROM comment WHERE id=" . $_POST ["message_id"] );
			}
			break;
	}
}
?>

