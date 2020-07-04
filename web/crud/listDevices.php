<?php
session_start();

if (isset($_GET['catagory']))
    $catagory = $_GET['catagory'];

if (isset($_SESSION['fullname']))
    $fullName = $_SESSION['fullname']; // holds url for last page visited.
else
    header("Location: https://dboard.csd.toronto.ca/login.php");

$dbConfFile = '../config/dbconf.php';

if (! file_exists($dbConfFile)) {
    $dbConfFile = 'config/dbconf.php';
}

include_once ($dbConfFile);

$dbName = 'tcsit';

$todayDate = date("Y-m-d");

$regex = new \MongoDB\BSON\Regex ( $catagory, 'i' );
if (isset($catagory)) {

    $command = new MongoDB\Driver\Command ( [
        'aggregate' => 'devices',
        'pipeline' => [
            [
                '$match' => [
                    'os' => $regex
                ]
            ],
            [
                '$sort' => [
                    'dindex' => 1
                ]
            ]
        ],
        'cursor' => new stdClass ()
    ] );
} else {
    $command = new MongoDB\Driver\Command ( [
        'aggregate' => 'devices',
        'pipeline' => [
            [
                '$sort' => [
                    'dindex' => 1
                ]
            ]
        ],
        'cursor' => new stdClass ()
    ] );
}


/*
  $command = new MongoDB\Driver\Command ( [
 'aggregate' => 'devices',
  'pipeline' => [
  [
  '$sort' => [
  'dindex' => 1
  ]
  ]
  ],
  'cursor' => new stdClass ()
  ] );
 */

echo "<html>";
echo "<head>";

echo "<link rel=\"stylesheet\" href=\"../css/tablesorter.css\" type=\"text/css\" media=\"print, projection, screen\" />";
echo "<link rel=\"stylesheet\" href=\"../css/jquery.tablesorter.pager.css\" type=\"text/css\" />";
echo '<link href="/css/buttons.css" type="text/css" rel="stylesheet" />';

echo '
<script>
$(function(){
    
  $("#Linux").click(function(){
     window.location = "/index.php?load=crud/listDevices.php&catagory=Linux";
     return false;
  });
    
  $("#Solaris").click(function(){
     window.location = "/index.php?load=crud/listDevices.php&catagory=Solaris";
     return false;
  });
    
  $("#Windows").click(function(){
     window.location = "/index.php?load=crud/listDevices.php&catagory=Windows";
     return false;
  });
    
  $("#Network").click(function(){
     window.location = "/index.php?load=crud/listDevices.php&catagory=Cisco";
     return false;
  });
    
});
</script>
';

echo "

<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery-ui/jquery-ui.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/parser-network.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.pager.js\" type=\"text/javascript\"></script>

<script>
function showEditBox(editobj,id) {
		$(editobj).prop('readonly','');
        $(\"#osContent_\"+ id ).show();
        var currentos = $(\"#osContent_\"+ id ).html();
        var editMarkUp = '<button name=\"ok\" onClick=\"callCrudAction(\'edit\',\'' + id + '\')\">Save</button><button name=\"cancel\" onClick=\"cancelEdit(\'' + id + '\')\">Cancel</button>';
        $(\"#osContent_\"+ id ).html(editMarkUp);
		

}

function cancelEdit(id) {
        $(\"#osContent_\"+ id ).hide();
        $(\"#osText_\"+ id ).prop('readonly','true');
}
		

function callCrudAction(action,id) {
        //$(\"#loaderIcon\").show();
        $(\"#osContent_\"+ id ).hide();
        $(\"#osText_\"+ id ).prop('readonly','true');
        var queryString;
        switch(action) {
                case \"add\":
                        queryString = 'action='+action+'&txtmessage='+ $(\"#txtmessage\").val();
                break;
                case \"edit\":
                        queryString = 'action='+action+'&id='+ id + '&os='+ $(\"#osText_\"+id).val();
                break;
                case \"delete\":
                        queryString = 'action='+action+'&message_id='+ id;
                break;
        }
        jQuery.ajax({
        url: \"/crud/crudDevices.php\",
        data:queryString,
        type: \"POST\",
        success:function(data){
                switch(action) {
                        case \"add\":
                                $(\"#comment-list-box\").append(data);
                        break;
                        case \"edit\":
                          $(\"#osContent_\"+ id ).hide();
                          $(\"#osText_\"+ id ).prop('readonly','true');
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
echo "<script>";
echo '
$(document).ready(function() 
    { 
        $("#contentTable").tablesorter( { 
widthFixed: true,
        headers: { 
            3: { 
                sorter: false 
            }, 
            4: { 
                sorter: false 
            }, 
            5: { 
                sorter: false 
            } 
        }
    })
//.tablesorterPager({container: $("#pager")});
}
); 
';
echo "</script>";
echo "</head>";

echo "<body>";
echo '
<table id="sliceByOS" class="menuInBody" style="width:100%">
<tr bgcolor="#CCEEFF">
<td align="right">
';

echo '<a href="#" id="Linux" class="menuitem"> Linux </a> | <a href="#" id="Solaris" class="menuitem"> Solaris </a> | <a href="#" id="Windows" class="menuitem"> Windows </a> | <a href="#" id="Printers" class="menuitem"> Printer </a> 
| <a href="#" id="Network" class="menuitem"> Network Devices </a>';

echo '
</td>
</tr>
</table>
';

try {
    $cursor = $manager->executeCommand($dbName, $command);

    $rowIndex = 0;

    echo "<table border=0 id=\"outLineTable\" class=\"centretable\" width=\"100%\">";
    echo "<tr>";
    echo "<td align=\"center\">";

    echo "<table border=1 id=\"contentTable\" class=\"tablesorter\"  width=\"100%\">";
    $rbgColor = "#FFFFFF";

    echo "<thead><tr>";
    echo "<th>Index</th><th>IP</th><th>Host Name</th><th>OS</th><th>Status</th><th>NIC</th><th>Ports</th><th>Device Type</th><th>Network Distance</th>";
    echo "</tr></thead>";
    echo "<tfoot><tr>";
    echo "<th>Index</th><th>IP</th><th>Host Name</th><th>OS</th><th>Status</th><th>NIC</th><th>Ports</th><th>Device Type</th><th>Network Distance</th>";
    echo "</tr></tfoot>";
    echo "<tbody>";

    $recordCount = 0;
    foreach ($cursor as $document => $value) {
        $recordCount += 1 ;
        
        if ($rowIndex == 1) {
            $rbgColor = "#CCEEFF";
            $rowIndex = 0;
        } else {
            $rbgColor = "#FFFFFF";
            $rowIndex = 1;
        }

        $portsTableString = "<table border=0>";
        $portsString = "";

        foreach ($value->ports as $port)
            $portsString = $portsString . "<tr><td>" . $port->portnumber . "</td><td>" . $port->protocol . "</td><td>" . $port->portstatus . "</td><td>" . $port->netservice . "</td></tr>";

        if (! $portsString == "") {
            $portsTableString = $portsTableString . $portsString . "</table>";
        } else {
            $portsTableString = "";
        }

        $nicTableString = "<table border=0>";
        $nicsString = "";

        foreach ($value->netcard as $nic)
            $nicsString = $nicsString . "<tr><td>" . $nic->macaddress . "</td><td>" . $nic->netcardmaker . "</td></tr>";

        if (! $nicsString == "") {
            $nicTableString = $nicTableString . $nicsString . "</table>";
        } else {
            $nicTableString = "";
        }

        echo "<tr bgcolor=" . $rbgColor . ">";

        // echo "<td>" . $value->ipaddress . "</td><td>" . $value->hostname . "</td><td>" . $value->os . "</td><td>" . $value->devstatus . "</td><td>" . $nicTableString . "</td><td>" . $portsTableString . "</td><td>" . $value->devicetype . "</td><td>" . $value->networkdistance . "</td>";
        echo "<td>" . $value->dindex . "</td>" . "<td>" . $value->ipaddress . "</td><td>" . $value->hostname . "</td><td>" . 
        "<div id=\"osTextDiv_" . $value->_id . "\"> <input id=\"osText_" . $value->_id . "\" type=\"text\" size=\"40\" value=\"" . $value->os . "\" readonly=\"true\" ondblclick=\"showEditBox(this,'" . $value->_id . "')\"> </div>" . 
        "<div id=\"osContent_" . $value->_id . "\"></div>" . "</td><td>" . $value->devstatus . "</td><td>" . $nicTableString . "</td><td>" . $portsTableString . 
        "</td><td>" . $value->devicetype . "</td><td>" . $value->networkdistance . "</td>";

        echo "</tr>";
    }
    echo "</tbody>";

    echo "</table>";
    echo '</td>';
    echo '</tr>';
    
    echo '<tr>';
    echo '<td align="center">';

    echo "";

    echo "<div id=\"pager\" class=\"pager\">
	<form>
		<img src=\"../themes/first.png\" class=\"first\"/>
		<img src=\"../themes/prev.png\" class=\"prev\"/>
		<input type=\"text\" class=\"pagedisplay\"/>
		<img src=\"../themes/next.png\" class=\"next\"/>
		<img src=\"../themes/last.png\" class=\"last\"/>
		<select class=\"pagesize\">
			<option selected=\"selected\"  value=\"10\">10</option>
			<option value=\"20\">20</option>
			<option value=\"30\">30</option>
			<option  value=\"40\">40</option>
		</select>
	</form>
</div>";
} catch (MongoDB\Driver\Exception\Exception $e) {
    echo $e->getMessage(), "\n";
}

echo '</td>';
echo '</tr>
</table>
';
echo "Total " . $recordCount . " Records Found.";

echo "</body>";
echo "</html>";
?>
