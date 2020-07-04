<?php
// Create connection to Oracle
session_start();
date_default_timezone_set('EST');

$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}

// Verify if the user logged in
$vSessionScript = 'verifySession.php';

require ($vSessionScript);

$commonFunctions = 'oraPasswordFuncs.php';

require ("config.php");
require ($commonFunctions);
require ("sharedFuncs.php");

require ("oraWalletEnv.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $key => $value)
        eval("\$$key = \"$value\";");
    // echo $key.'='.$value.'<br />';
}

if (isset($_SESSION['dbString'])) {
    $dbString = $_SESSION['dbString'];
}

if (empty($userName)) {
    if (isset($_SESSION['tgtuid'])) {
        $userName = $_SESSION['tgtuid'];
    } else {
        if (isset($_SESSION['uid'])) {
            $userName = strtoupper($_SESSION['uid']);
        } else {
            echo "Please login to kill database sessions.";
            exit();
        }
    }
} else {
    // Save the target uid for non-end user use
    $_SESSION['tgtuid'] = strtoupper($userName);
}

// include '/oraoperation/getOraUserStatus.php';

$userName = strtoupper($userName);

echo "
<script>
$(document).ready(function() {
    $('td :checkbox').change(function() {
        $(this).closest('tr').toggleClass('highlight');
    }); 
});
</script>
";

echo "
<script>

$(function(){

        $(document).off('click', \"#selectAll\").on('click', \"#selectAll\", function (e) {
                $(':checkbox').prop('checked', this.checked);
                $(\"#allSessions\").hide();
                $(\"#clearSessions\").show();
        });

        $(document).off('click', \"#clearSessions\").on('click', \"#clearSessions\", function (e) {
                $(':checkbox').prop('checked', this.checked);
                $(\"#allSessions\").show();
                $(\"#clearSessions\").hide();
        });

        $(document).off('click', \"#checkAll\").on('click', \"#checkAll\", function (e) {
	       	$('input:checkbox').not(this).prop('checked', this.checked);
    	});

        $(document).off('click', \"#refreshSessions\").on('click', \"#refreshSessions\", function (e) {
        	e.preventDefault();
                $(\"#returnDiv\").load(\"/oraoperation/showUserActiveSessions.php\");
	});

        //Remove attached event handler first https://www.gajotres.net/prevent-jquery-multiple-event-triggering/

        $(document).off('click', \"#showPassword\").on('click', \"#showPassword\", function (e) {
            var x = $(\"#userNewPassword\").prop('type');
            if (x === \"password\") {
                $(\"#userNewPassword\").prop('type', \"text\");
            } else {
                $(\"#userNewPassword\").prop('type', \"password\");
            }
        });


        //Remove attached event handler

        $(document).off('click', \"#killSelectSessions\").on('click', \"#killSelectSessions\", function (e) {
        	e.preventDefault();
	        //$(\"#messageDiv\").hide();

        	var pwdData=$(\":input\").serializeArray();
                var userName=$(\"#userName\").val();

                // Kill Database Session
                $.ajax({
                        type: \"POST\",
                        url: \"/oraoperation/killUserSessions.php\",
                        dataType : 'html',
                        data: pwdData,
                        success: function(data, textStatus, jQxhr ){
                            if ($.trim(data) == 'SUCCESS') {
                                $(\"#returnDiv\").load(\"/oraoperation/showUserActiveSessions.php\");
                                msg = userName + '\'s selected Oracle DB Sessions have been killed successfully.';
                                $(\"#messageDiv\").show();
                                $(\"#messageDiv\").html(msg);
                            } else {
                                //$(\"#returnDiv\").load(\"/oraoperation/showUserActiveSessions.php\");
                                $(\"#messageDiv\").html(data);
                            }
                        },
                        error: function( jqXhr, textStatus, errorThrown ){
                                $(\"#messageDiv\").html(errorThrown);
                            console.log( errorThrown );
                        }
                });

        
                // Kill IAS Session
        	$.ajax({
                	type: \"POST\",
	                url: \"/oraoperation/killIASProcess.php\",
        	        dataType : 'html',
                	data: pwdData,
	                success: function(data, textStatus, jQxhr ){
			    $(\"#refreshSessions\" ).trigger(\"click\" );
			    //alert(data);
        	            if ($.trim(data) == '0') {
                        	//$(\"#returnDiv\").load(\"/oraoperation/showUserActiveSessions.php\");
                                msg = userName + '\'s selected Oracle IAS Sessions have been killed successfully.';
	                        $(\"#killIASMessageDiv\").show();
	                        $(\"#killIASMessageDiv\").html(msg);
                	    } else {
                        	//$(\"#returnDiv\").load(\"/oraoperation/showUserActiveSessions.php\");
                        	$(\"#killIASMessageDiv\").html(data);
	                    }
        	        },
                	error: function( jqXhr, textStatus, errorThrown ){
                        	$(\"#killIASMessageDiv\").html(errorThrown);
	                    console.log( errorThrown );
        	        }
               });
	});

        //Remove attached event handler

        $(document).off('click', \"#unlockOraAccount\").on('click', \"#unlockOraAccount\", function (e) {
                e.preventDefault();
                $(\"#userStatusDiv\").hide();

                var userName=$(\"#userName\").val();
                formData = [];
                $.each(['userName'], function(index, value) {
                    formData.push({name: value,value: eval(value) });
                });

                $.ajax({
                        type: \"POST\",
                        url: \"/oraoperation/unlockOraUser.php\",
                        dataType : 'html',
                        data: formData,
                        success: function(data){
                            if ($.trim(data) == \"SUCCESS\") {
                        	    msg = userName + ' has been unlocked.';
                                $(\"#messageDiv\").html(msg);
                            } else {
                                $(\"#messageDiv\").html(data);
                            }
                        },
                        error: function( jqXhr, textStatus, errorThrown ){
                                $(\"#messageDiv\").html(errorThrown);
                            console.log( errorThrown );
                        }
                });
        });


        //Remove attached event handler first to avoid multiple firing

        $(document).off('click', \"#cancelPasswordChange\").on('click', \"#cancelPasswordChange\", function (e) {
            $(\"#userNewPassword\").val('');
        });

        $(document).off('click', \"#resetOraUserPassword\").on('click', \"#resetOraUserPassword\", function (e) {
                //$(\"#inputUserPassword\").toggle('show');
                $(\"#inputUserPassword\").show();
                $(\"#messageDiv\").html('');
        });

        $(document).off('click', \"#confirmPasswordChange\").on('click', \"#confirmPasswordChange\", function (e) {
                e.preventDefault();
                $(\"#userStatusDiv\").hide();

                var userName=$(\"#userName\").val();
                var newPWD=$(\"#userNewPassword\").val();
                formData = [];
                $.each(['userName', 'newPWD'], function(index, value) {
                    formData.push({name: value,value: eval(value) });
                });

                $.ajax({
                        type: \"POST\",
                        url: \"/security/resetOraclePasswd.php\",
                        dataType : 'html',
                        data: formData,
                        success: function(data){
                            $(document).off('click', \"#confirmPasswordChange\");
                            if ($.trim(data) == \"SUCCESS\") {
                                //$(\"#returnDiv\").load(\"/oraoperation/showUserActiveSessions.php\");
                        	    msg = userName + ' password has been changed.';
                                //$(\"#userStatusDiv\").show();
                                //$(\"#userStatusDiv\").html(msg);
                                $(\"#messageDiv\").html(msg);
                            } else {
                                //$(\"#returnDiv\").load(\"/oraoperation/showUserActiveSessions.php\");
                                $(\"#messageDiv\").html(data);
                            }
                        },
                        error: function( jqXhr, textStatus, errorThrown ){
                                $(\"#messageDiv\").html(errorThrown);
                            console.log( errorThrown );
                        }
                });
        });

});
</script>
";

if (isset($_SESSION['dbString'])) {
    $dbString = $_SESSION['dbString'];
}

$conn = oci_connect("/", "", $dbString, null, OCI_CRED_EXT);

if (! $conn) {
    $m = oci_error();
    
    echo $m['message'] . "\n";
    exit();
} else {
    $sql = 'BEGIN administer_users.GET_ACCOUNT_STATUSES(:P_OUT,:P_USERNAME); END;';
    $stmt = oci_parse($conn, $sql);
    
    // Bind the output parameter
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":P_USERNAME", $userName);
    oci_bind_by_name($stmt, ":P_OUT", $cursor, - 1, OCI_B_CURSOR);
    
    $return = oci_execute($stmt);
    if ($return) {
        
        oci_execute($cursor);
        
        $return_array = array();
        
        while (($row = oci_fetch_array($cursor, OCI_BOTH)) != false) {
            $row_array['id'] = $row['USERNAME'];
            $row_array['value'] = $row['USERNAME'];
            $row_array['label'] = $row['USERNAME'];
            
            // array_push($return_array,$row['USERNAME'],$row['ACCOUNT_STATUS']);
            array_push($return_array, $row_array);
            $userStatus = $row['USERNAME'] . ":" . $row['ACCOUNT_STATUS'];
            $_SESSION['orauserstatus'] = $userStatus;
        }
        // echo json_encode($return_array);
    } else {
        $e = oci_error($stmt); // For oci_parse errors pass the connection handle
                               // trigger_error(htmlentities($e['message']), E_USER_ERROR);
        pushToLogFile("ERROR: " . $e['message']);
        
        echo "ERROR: " . $e['message'];
    }
}

// Close the Oracle connection and resources
// oci_free_statement($stmt);
// oci_free_statement($cursor);
// oci_close($conn);

echo "<div id=\"userStatusDiv\" >";

// EXPIRED & LOCKED
if (isset($_SESSION['orauserstatus'])) {
    $pieces = explode(":", $_SESSION['orauserstatus']);
    // if ($pieces[1] != "OPEN") {
    $unlockButton = "";
    if ($pieces[1] == "LOCKED") {
        //$unlockButton = '<form id="unlockUserAccount"><button type="submit" id="unlockOraAccount" name="unlockOraAccount"> <img src="/images/unlock.svg" alt="Unlock" height="24" width="24"> </form>';
        $unlockButton = '<button type="submit" id="unlockOraAccount" name="unlockOraAccount"> <img src="/images/unlock.svg" alt="Unlock" height="16" width="16">';
        
        $lockedText = '<alert>' . $pieces[1] . '</alert>';
        echo "<table border=0 id=\"userStatusTable\" class=\"centretable\" width=\"400\">";
        echo "<tr>";
        echo "<td align=\"right\">";
        echo $pieces[0] . ":" . $lockedText;
        echo "</td>";
        echo "<td align=\"left\">";
        echo $unlockButton;
        echo "</td>";
        echo "</tr>";
        echo "</table>";
    } else {
        
        $resetPwdButton = "";
        // if ($pieces[1] == "EXPIRED") {
        // if (strpos($pieces[1], 'EXPIRED') !== FALSE) {
        // $resetPwdButton = '<form id="resetUserPassword"><button type="submit" id="resetOraUserPassword" name="resetOraUserPassword"> <img src="/images/resetpasswd.svg" alt="Reset Password" height="16" width="16"> </form>';
        $resetPwdButton = '<button type="submit" id="resetOraUserPassword" name="resetOraUserPassword"> <img src="/images/resetpasswd.svg" alt="Reset Password" height="16" width="16">';
        $editMarkUp = '<button name=\"ok\" id="confirmPasswordChange" ><img src="/images/check.svg" alt="Change" height="16" width="16"></button>' . '<button name=\"cancel\" id="cancelPasswordChange"><img src="/images/x.svg" alt="Cancel" height="16" width="16"></button>' . '<button name=\"togglepassword\" id="showPassword"><img src="/images/text.svg" alt="Show Password" height="16" width="16"></button>';
        
        $lockedText = '<alert>' . $pieces[1] . '</alert>';
        echo "<table border=0 id=\"userStatusTable\" class=\"centretable\" width=\"400\">";
        echo "<tr>";
        echo "<td align=\"middle\">";
        echo $pieces[0] . ":" . $lockedText;
        // echo "</td>";
        // echo "<td align=\"left\">";
        echo $resetPwdButton;
        echo "</td>";
        echo "</tr>";
        echo '<tbody id="inputUserPassword" style="display: none;">';
        echo "<tr>";
        echo "<td align=\"middle\">";
        echo 'New Password: <input id="userNewPassword" type="password" size="20" value="">';
        echo $editMarkUp;
        echo "</td>";
        echo "</tr>";
        echo "</tbody>";
        echo "</table>";
        // }
        // }
    }
}
echo "</div>";

$conn = oci_connect("/", "", $dbString, null, OCI_CRED_EXT);
// $conn = oci_connect("/", "", "ITSERVICE", "AL32UTF8", OCI_CRED_EXT);

echo "<div id=\"userDBSessionsDiv\" >";

if (! $conn) {
    $m = oci_error();
    
    echo $m['message'] . "\n";
    exit();
} else {
    $sql = 'BEGIN administer_users.get_own_sessions(:P_USERNAME, :P_OUT); END;';
    $stmt = oci_parse($conn, $sql);
    
    // Bind the input parameter
    oci_bind_by_name($stmt, ":P_USERNAME", $userName);
    
    $cursor = oci_new_cursor($conn);
    oci_bind_by_name($stmt, ":P_OUT", $cursor, - 1, OCI_B_CURSOR);
    
    // Application User ID won't have check all feature
    $appIdRexp = "/^csis/i";
    
    $return = oci_execute($stmt);
    if ($return) {
        
        oci_execute($cursor);
        
        echo "<table border=0 id=\"outLineTable\" class=\"centretable\" width=\"100%\">";
        echo "<tr>";
        echo "<td align=\"center\">";
        
        echo "<form id=\"sessionOperation\">";
        echo "<table border=1 id=\"sessionContentTable\">";
        echo "<thead><tr>";
        if (preg_match($appIdRexp, $userName)) {
            echo "<th>PID</th><th>SID</th><th>SER#</th><th>Connect From</th><th>User Name</th><th>OS User</th><th>Program</th><th></th>";
        } else {
            echo "<th>PID</th><th>SID</th><th>SER#</th><th>Connect From</th><th>User Name</th><th>OS User</th><th>Program</th><th><input type=\"checkbox\" id=\"checkAll\" ></th>";
        }
        echo "</tr></thead>";
        echo "<tfoot><tr>";
        echo "<th>PID</th><th>SID</th><th>SER#</th><th>Connect From</th><th>User Name</th><th>OS User</th><th>Program</th><th></th>";
        echo "</tr></tfoot>";
        // echo "<tbody>";
        
        $nosession = true;
        while (($row = oci_fetch_array($cursor, OCI_BOTH)) != false) {
            echo "<tr>";
            echo "<td>" . $row['PID'] . "</td><td>" . $row['SID'] . "</td><td>" . $row['SER#'] . "</td><td>" . $row['BOX'] . "</td><td>" . $row['USERNAME'] . "</td><td>" . $row['OS_USER'] . "</td><td>" . $row['PROGRAM'] . "</td><td>" . "<input type=\"checkbox\" name=\"session_list[]\" value=\"" . $row['USERNAME'] . "," . $row['SID'] . "," . $row['SER#'] . "," . $row['PID'] . "," . $row['PROGRAM'] . "\">" . "</td>";
            
            echo "</tr>";
            $nosession = false;
        }
        // echo "</tbody>";
        
        echo "</table>";
        echo "</form>";
        
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=\"center\">";
        if ($nosession) {
            echo "<input type=\"submit\" id=\"killSelectSessions\" name=\"killSelectSessions\" value=\"Kill Selected Sessions\" disabled/>    ";
            echo "<input type=\"submit\" id=\"refreshSessions\" name=\"refreshSessions\" value=\"Refresh Sessions\"/>";
            // echo "<button id=\"refreshSessions\" name=\"refreshSessions\" > Refresh Sessions</button>";
        } else {
            echo "<input type=\"submit\" id=\"killSelectSessions\" name=\"killSelectSessions\" value=\"Kill Selected Sessions\"/>    ";
            echo "<input type=\"submit\" id=\"refreshSessions\" name=\"refreshSessions\" value=\"Refresh Sessions\"/>";
        }
        
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=\"center\">";
        echo "<div id=\"messageDiv\" class=\"mssgDiv\">";
        echo "<md></md>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td align=\"center\">";
        echo "<div id=\"killIASMessageDiv\" class=\"iasMssgDiv\">";
        echo "<md></md>";
        echo "</div>";
        echo "</td>";
        echo "</tr>";
        
        echo "</table>";
    } else {
        echo "Failed to get your sessions, please contact DBA.";
    }
}

echo "</div>";

// Close the Oracle connection
oci_free_statement($stmt);
oci_free_statement($cursor);
oci_close($conn);
?>
