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
        $('#urlEdit_'+id).hide();
        $(editobj).prop('disabled','true');
        var currentUrl = $(\"#urlContent_\"+ id ).html();
        var editMarkUp = '<textarea rows=\"2\" cols=\"60\" id=\"url_'+id+'\">'+currentUrl+'</textarea><button name=\"ok\" onClick=\"callCrudAction(\'edit\',\'' + id + '\')\">Save</button><button name=\"cancel\" onClick=\"cancelEdit(\'' + currentUrl + '\',\'' + id + '\')\">Cancel</button>';
        $(\"#urlContent_\"+ id ).html(editMarkUp);
}

function cancelEdit(url, id) {
        $(\"#urlContent_\"+ id ).html(url);
        $(\"#urlEdit_\"+id).show();
        $(\"#btnEditAction_\"+id).prop('disabled','');
}

function callCrudAction(action,id) {
        //$(\"#loaderIcon\").show();
        var queryString;
        switch(action) {
                case \"add\":
                        queryString = 'action='+action+'&txtmessage='+ $(\"#txtmessage\").val();
                break;
                case \"edit\":
                        queryString = 'action='+action+'&id='+ id + '&appurl='+ $(\"#url_\"+id).val();
                break;
                case \"delete\":
                        queryString = 'action='+action+'&message_id='+ id;
                break;
        }
        jQuery.ajax({
        url: \"latestWASStatusCrud.php\",
        data:queryString,
        type: \"POST\",
        success:function(data){
                switch(action) {
                        case \"add\":
                                $(\"#comment-list-box\").append(data);
                        break;
                        case \"edit\":
        $(\"#urlContent_\"+ id ).html(data);
        $(\"#urlEdit_\"+id).show();
        $(\"#btnEditAction_\"+id).prop('disabled','');
                        break;
                        case \"delete\":
                                $('#message_'+id).fadeOut();
                        break;
                }
                //$(\"#txtmessage\").val('');
                $(\"#loaderIcon\").hide();
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
		
		$urlColor = "#00FF00";
		if ($value->appurlvalidity == "OK") {
			$urlColor = "#0E8001";
		} else {
			$urlColor = "#FF0000";
		}
		
		$iMServerName = $value->cell . "\\" . $value->node . "\\" . $value->server;
		
		if ($iMServerName == $mServerName) {
			$displayServerName = "";
		} else {
			$displayServerName = $iMServerName;
		}
		
		if (trim ( $value->appurl ) == false) {
			$appUrlLink = $value->appname;
		} else {
			$appUrlLink = "<a href=\"" . $value->appurl . "\"  target=\"_blank\">" . $value->appname . "</a>";
		}
		echo "<tr bgcolor=" . $rbgColor . ">";
		echo "<td>" . $value->timestamp . "</td><td><a href=" . $value->adminconsole . "  target=\"_blank\">" . $displayServerName . "</a></td><td>" . $appUrlLink . "</td> <td><font color=" . $statusColor . ">" . $value->status . "</font></td><td><font color=" . $urlColor . ">" . $value->appurlvalidity . "</font></td><td><div id=\"urlEdit_" . $value->_id . "\">  <button id=\"btnEditAction_" . $value->_id . "\" name=\"edit\" onClick=\"showEditBox(this,'" . $value->_id . "')\">Edit</button></div><div id=\"urlContent_" . $value->_id . "\">" . $value->appurl . "</div></td>";
		
		echo "</tr>";
		$mServerName = $iMServerName;
	}
	echo "</table>";
	echo "";
	echo "</center>";
} catch ( MongoDB\Driver\Exception\Exception $e ) {
	echo $e->getMessage (), "\n";
}

?>
