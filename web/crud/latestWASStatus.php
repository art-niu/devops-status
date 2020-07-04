<?php
$dbConfFile='../config/dbconf.php';

date_default_timezone_set('EST');
if (!file_exists($dbConfFile)) {
    $dbConfFile='config/dbconf.php';
}

include_once($dbConfFile);

$dbName = 'tcsit';

$todayDate = date ( "Y-m-d" );

$regex = new \MongoDB\BSON\Regex ( '^' . $todayDate, 'm' );

$command = new MongoDB\Driver\Command ( [ 
		'aggregate' => 'latestWASStatus',
		'pipeline' => [ 
				['$match' => ['timestamp' => $regex ]],
				[ 
						'$sort' => [ 
								'priority' => 1,
								'timestamp' => - 1,
								'cell' => 1,
								'node' => 1,
								'server' => 1,
								'appname' => 1 
						] 
				] 
		],
		'cursor' => new stdClass () 
] );

echo "
<script src=\"../jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>

<script>
function showEditBox(editobj,id) {
        $(editobj).prop('readonly','');
        $(\"#urlContent_\"+ id ).show();
        var currentUrl = $(\"#urlContent_\"+ id ).html();
        var editMarkUp = '<button name=\"ok\" onClick=\"callCrudAction(\'edit\',\'' + id + '\')\">Save</button><button name=\"cancel\" onClick=\"cancelEdit(\'' + id + '\')\">Cancel</button>';
        $(\"#urlContent_\"+ id ).html(editMarkUp);
}

function cancelEdit(id) {
        $(\"#urlContent_\"+ id ).hide();
        $(\"#urlText_\"+ id ).prop('readonly','true');
        //$(\"#urlEdit_\"+id).show();
        //$(\"#btnEditAction_\"+id).prop('disabled','');
}

function callCrudAction(action,id) {
        //$(\"#loaderIcon\").show();
        $(\"#urlContent_\"+ id ).hide();
        $(\"#urlText_\"+ id ).prop('readonly','true');
        var queryString;
        switch(action) {
                case \"add\":
                        queryString = 'action='+action+'&txtmessage='+ $(\"#txtmessage\").val();
                break;
                case \"edit\":
                        queryString = 'action='+action+'&id='+ id + '&appurl='+ $(\"#urlText_\"+id).val();
                break;
                case \"delete\":
                        queryString = 'action='+action+'&message_id='+ id;
                break;
        }
        jQuery.ajax({
        url: \"/crud/latestWASStatusCrud.php\",
        data:queryString,
        type: \"POST\",
        success:function(data){
                switch(action) {
                        case \"add\":
                                $(\"#comment-list-box\").append(data);
                        break;
                        case \"edit\":
                          $(\"#urlContent_\"+ id ).hide();
                          $(\"#urlText_\"+ id ).prop('readonly','true');
                        break;
                        case \"delete\":
                                $('#message_'+id).fadeOut();
                        break;
                }
                //$(\"#txtmessage\").val('');
                //$(\"#loaderIcon\").hide();
        },
        error:function (){}
        });
}

</script>
";

try {
	$cursor = $manager->executeCommand ( $dbName, $command );
	
	$rowIndex = 0;
	echo '<link href="/css/buttons.css" type="text/css" rel="stylesheet" />';
	echo "<table border=1 >";
	$rbgColor = "#FFFFFF";
	echo "<center>";
	// Table Header
	echo "<td><b>Timestamp</b></td><td><b>Application Server</b></td><td><b>Application</b></td><td><b>Status</b></td><td><b>URL Validity</b></td><td><b>Application URL</b></td>";
	// Merged Servername cellname\nodename\servername
	$mServerName = "";
	$iMServerName = "";
	$recordCount=0;
	foreach ( $cursor as $document => $value ) {
	    $recordCount += 1;
		if ($rowIndex == 0) {
			$rbgColor = "#CCEEFF";
			$rowIndex = 1;
		} else {
			$rbgColor = "#FFFFFF";
			$rowIndex = 0;
		}
		
		$statusColor = "#00FF00";
		if ($value->status == "Stopped") {
			$statusColor = "#FF0000";
			$appStatus = '<img src="/images/arrow-down-red.jpg" alt="' . $value->status . '" height="24" width="24">'; 
		} else {
			$statusColor = "#0E8001";
			$appStatus = '<img src="/images/arrow-up-green.jpg" alt="' . $value->status . '" height="24" width="24">'; 
		}
		
		$serverStatusColor = "#00FF00";
		if ($value->status == "Stopped") {
		    $serverStatusColor = "#FF0000";
		} else {
		    $serverStatusColor = "#0E8001";
		}
		
		$urlColor = "#00FF00";
		if ($value->appurlvalidity == "OK") {
			$urlColor = "#0E8001";
			$urlValidity = '<img src="/images/check.jpg" alt="Reachable" height="24" width="24">'; 
		} elseif ($value->appurlvalidity != "") {
			$urlColor = "#FF0000";
			$urlValidity = '<img src="/images/x.jpg" alt="Unreachable" height="24" width="24">'; 
		} else {
			$urlValidity = '';
		}
		
		$iMServerName = $value->cell . "\\" . $value->node . "\\" . $value->server;
		
		if ($iMServerName == $mServerName) {
			$displayServerName = "";
		} else {
			$displayServerName = $iMServerName;
		}
		
		if (is_null( $value->appurl )) {
			$appUrlLink = $value->appname;
			$appUrlVal = "";
		} else {
			$appUrlLink = "<a href=\"" . $value->appurl . "\"  target=\"_blank\">" . $value->appname . "</a>";
			$appUrlVal = trim ( $value->appurl );
		}
		echo "<tr bgcolor=" . $rbgColor . ">";
		echo "<td>" . $value->timestamp . "</td><td><a href=" . $value->adminconsole . ">" . "<font color=" . $serverStatusColor . ">" . $displayServerName . "</a></td><td>" . $appUrlLink . "</td> <td>" . $appStatus . "</td><td>" . $urlValidity . "</td><td>" . "<div id=\"urlTextDiv_" . $value->_id . "\"> <input id=\"urlText_" . $value->_id . "\" type=\"text\" size=\"40\" value=\"" . $appUrlVal . "\" readonly=\"true\" ondblclick=\"showEditBox(this,'" . $value->_id . "')\"> </div>" . "<div id=\"urlContent_" . $value->_id . "\"></div></td>";
		// echo "<td>" . $value->timestamp . "</td><td><a href=" . $value->adminconsole . " target=\"_blank\">" . $displayServerName . "</a></td><td>" . $appUrlLink . "</td> <td><font color=" . $statusColor . ">" . $value->status . "</font></td><td><font color=" . $urlColor . ">" . $value->appurlvalidity . "</font></td><td><div id=\"urlEdit_" . $value->_id . "\"> <button id=\"btnEditAction_" . $value->_id . "\" name=\"edit\" onClick=\"showEditBox(this,'" . $value->_id . "')\">Edit</button></div><div id=\"urlContent_" . $value->_id . "\">" . $value->appurl . "</div></td>";
		
		echo "</tr>";
		$mServerName = $iMServerName;
	}
	echo "</table>";
	echo "Total " . $recordCount . " Records Found.";
	echo "</center>";
} catch ( MongoDB\Driver\Exception\Exception $e ) {
	echo $e->getMessage (), "\n";
}

?>
