<?php
session_start ();

$currentWorkingDir = dirname ( dirname ( __FILE__ ) );
$configDir = $currentWorkingDir . "/config";

if (realpath ( $configDir )) {
	set_include_path ( get_include_path () . PATH_SEPARATOR . $configDir );
}

if ($_SERVER ['REQUEST_METHOD'] === 'POST') {
	foreach ( $_POST as $key => $value )
		eval ( "\$$key = \"$value\";" );
} else if ($_SERVER ['REQUEST_METHOD'] === 'GET') {
	$confirmationCode = $_GET ['confirmationCode'];
}

$dbConfFile = 'dbconf.php';

require ($dbConfFile);
$commonDir = $currentWorkingDir . "/common";
if (realpath ( $commonDir )) {
	set_include_path ( get_include_path () . PATH_SEPARATOR . $commonDir );
}
// Verify if the user logged in
$vSessionScript = 'verifySession.php';

require ($vSessionScript);

$collection = "tcsit.resetPasswordCodes";

$currentTime = round ( microtime ( true ) );

$filter = [ 
		'confirmationCode' => $confirmationCode,
		'userName' => $userId,
		'resetAt' => '',
		'expiryTime' => [ 
				'$gte' => $currentTime 
		] 
];

$options = [ 
		/* Only return the following fields in the matching documents */
		'projection' => [ 
				'_id' => 1,
				'userMail' => 1 
		] 
];

$query = new MongoDB\Driver\Query ( $filter, $options );

$readPreference = new MongoDB\Driver\ReadPreference ( MongoDB\Driver\ReadPreference::RP_PRIMARY );
try {
	$cursor = $manager->executeQuery ( 'tcsit.resetPasswordCodes', $query, $readPreference );
	// $cursor = $manager->executeQuery('tcsit.resetPasswordCodes', $query);
	
	$referencecode = $cursor->toArray ();
	
	if (count ( $referencecode ) == 1) {
		$_SESSION ['cCode'] = $confirmationCode;
		foreach ( $referencecode as $reference ) {
			$_SESSION ['_id'] = $reference->_id;
			// echo $reference->userName;
			echo "VALID";
		}
		;
	} elseif (count ( $referencecode ) > 1) {
		echo "Multiple";
		$_SESSION ['cCode'] = 'INVALID';
	} else {
		$_SESSION ['cCode'] = 'INVALID';
		echo "NotFound";
	}
} catch ( MongoDB\Driver\Exception\Exception $e ) {
	echo $e->getMessage (), "\n";
}

?>
