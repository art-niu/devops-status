<?php

$requiredRoles = array("helpdesk", "dba", "infrastructure");

$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}
$vSessionScript = 'verifySession.php';

require ($vSessionScript);


$epocStart = time();
$consoleLog = "decryptDatabaseBackup_" . $epocStart;
$logFile = "/logs/" . $consoleLog;

echo "
<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.validate.min.js\" type=\"text/javascript\"></script>
";

echo "
<script>
//$(function(){
//    $('#dstServerTip').tooltip();
//});

$(function(){

    // Setup form validation on the form element
    $('#decryptDBBackupsForm').validate({

        rules: {
            dstServerName: {
                required: true,
                notEqualString: \"--\"
            },
            encryptedBackup: {
                startWith: \"/\",
                minlength: 3,
                required: true
            },
            decryptedLocation:  {
                startWith: '/',
                required: true,
                minlength: 3
            }
        },

            //decryptedLocation: unixFullPath

        // Specify the validation error messages
        messages: {
                            dstServerName: 'You have to choose the server from the dropdown menu. Please contact support team if you cannot find it in the list.',
                            encryptedBackup: {
                                required: 'Encrypted Backup: Cannot be empty',
                                minlength: 'Encrypted Backup: The input is too short.',
                                startWith: 'Encrypted Backup: Please input valid full path in unix format. i.e. /path/to/encrypted/backup/location'
                            },
                            decryptedLocation: {
                                required: 'Decrypted Location: Cannot be empty.',
                                minlength: 'Decrypted Location: The input is too short.',
                                startWith: 'Decrypted Location: Please input valid full path in unix format. i.e. /path/to/encrypted/backup/location'
                            },
        },
    	errorElement : 'div',
    	errorLabelContainer: '#validateMsgDiv'
    });

	// Add validation rules
        $.validator.addMethod('pattern',function(value,element,param){
            if (this.optional(element)) {
                return true;
                }
            if (typeof param === 'string') {
                param = new RegExp('^(?:' + param + ')$');
                }
                return param.test(value);
                },'Invalid Format');

	$.validator.addMethod('startWith',function(value,element,param){
	    if (this.optional(element)) {
                return true;
        	}
	    if (typeof param === 'string') {
                param = new RegExp('^' + param + '.*$');
	        }
	        return param.test(value);
		},'Invalid Format');

	$.validator.addMethod('hasUppercase', function(value, element) {
        	if (this.optional(element)) {
                	return true;
	        }
		return /[A-Z]/.test(value);
		}, 'Must contain UPPERCASE');

	$.validator.addMethod('hasLowercase', function(value, element) {
        	if (this.optional(element)) {
                	return true;
	        }
	    	return /[a-z]/.test(value);
		}, 'Must contain lowercase');

	$.validator.addMethod('startWithAlphabet', function(value, element) {
        	if (this.optional(element)) {
                	return true;
	        }
	    	return /^[A-Za-z]/.test(value);
		}, 'Must Start With Alphabet');

	$.validator.addMethod('hasNumber', function(value, element) {
        	if (this.optional(element)) {
                	return true;
	        }
	    	return /[0-9]/.test(value);
		}, 'Must contain number');

        $.validator.addMethod('notEqual', function(value, element, param) {
                return this.optional(element) || value != $(param).val();
                }, 'You cannot reuse previous password');

        $.validator.addMethod('notEqualString', function(value, element, param) {
                return param != value;
                }, 'Valud cannot be {0}');

        $.validator.addMethod('hasNoOracleSpecialChar', function(value, element) {
                if (this.optional(element)) {
                        return true;
                }
                if (/[_\$\#,&\"\\\]/.test(value)) {
                        return false;
                } else {
                        return true;
                } 
                }, '\\\,\"#\$\_& are not allowed');

        $(document).on('click', \"#resetinputs\", function (e) {

		      $(\"#encryptedBackup\").val(\"\");
		      $(\"#dstServerName\").val(\"\");
		      $(\"#decryptedLocation\").val(\"\");
	   });


        $(document).on('change',$(\"input[name=fileSet]:radio\"), function (e) {
            var textNewer = '<font color=\"#D3D3D3\">&#x2022;If the input is folder, all the files in the folder will be decrypted.<br> &#x2022;If the input is a file, all the files newer than it will be decrypted.</font>';
            var textOne = '<font color=\"#D3D3D3\">&#x2022;The input must be full path to the specified file.</font>';
            var textWhole = '<font color=\"#D3D3D3\">&#x2022;The input must be full path to the encrypted source folder.<br> &#x2022;If the input is a file, all the files in the same foler will be decrypted.</font>';
            var sourceTips = [textNewer, textOne, textWhole];

            var chosenOption = $('input[name=fileSet]:checked', '#decryptDBBackupsForm').val();
            $(\"#decryptionTips\").html(sourceTips[chosenOption]);
        });


        $(document).off('click', \"#runTask\").on('click', \"#runTask\", function (e) {
        	e.preventDefault();
                var encryptedBackup=$(\"#encryptedBackup\").val();
                var dstServerName=$(\"#dstServerName\").val();
                var decryptedLocation=$(\"#decryptedLocation\").val();
                var chosenOption = $('input[name=fileSet]:checked', '#decryptDBBackupsForm').val();

                var fileSets = ['newer', 'one', 'whole'];
                var fileSet = fileSets[chosenOption];

                $(\"#buttonsSubmitDiv\").hide();
                $(\"#buttonsResetDiv\").hide();
                $(\"#jobProgress\").show();
                $(\"#jobProgress\").load(\"/common/progress.php?desc=Decryption%20RUNNING\");
       			$(\"#responseDiv\").html('');

                if($(\"#decryptDBBackupsForm\").valid()) {
                    var logfile = '"  . $logFile . "' ;
                    var consoleLog = '"  . $consoleLog . "';
                    $(\"#responseDiv\").show();
                    $(\"#responseDiv\").load(\"/common/tailLog.php?logfile=\" + logfile);
";

echo "

                    formData = [];
                    $.each(['fileSet', 'encryptedBackup', 'dstServerName', 'decryptedLocation','consoleLog'], function(index, value) {
                        formData.push({name: value,value: eval(value) });
                    });

        	        $.ajax({
                	   type: \"POST\",
	                   url: \"/oraoperation/decryptFolderOrFile.php\",
        	           dataType : 'html',
                	   data: formData,
	                   success: function(data, textStatus, jQxhr ){

    			 var returnStrings = data.split(\":\");

    			 if ( returnStrings[1]  == 'SUCCEEDED') {
                        	 titleMsg = '<p class=\"statusSuccessfulTitle\">Job Completed.</p>';
                         	 $(\"#jobProgress\").html(titleMsg);
                        	 msg = '<p class=\"statusSuccessfulMessage\">Decryption Succeeded.</p>';
        			 $(\"#responseDiv\").html(msg);
			         $(\"#resetinputs\" ).trigger(\"click\" );
                             $(\"#buttonsSubmitDiv\").show();
                             $(\"#buttonsResetDiv\").show();
                         } else {
                        	 titleMsg = '<p class=\"statusFailedTitle\">Job Completed with Failure.</p>';
                         	 $(\"#jobProgress\").html(titleMsg);
                        	 msg = '<p class=\"statusFailedMessage\"> Decryption Failed..</p>';
        			 $(\"#responseDiv\").html(msg);
			         $(\"#resetinputs\" ).trigger(\"click\" );
                             $(\"#buttonsSubmitDiv\").show();
                             $(\"#buttonsResetDiv\").show();
        	         }
        	           },
                	   error: function( jqXhr, textStatus, errorThrown ){
                             titleMsg = '<p class=\"statusFailedTitle\">Job Completed with Failure.</p>';
                             $(\"#jobProgress\").html(titleMsg);
                             $(\"#buttonsSubmitDiv\").show();
                             $(\"#buttonsResetDiv\").show();
                              msg = 'Decryption Failed.';
        		     $(\"#responseDiv\").html(msg);
			     $(\"#backupInfo\").show();
                             $(\"#messageDiv\").html(errorThrown);
    	                     console.log( errorThrown );
            	        }
                    });
                } 
	    });

});


</script>
";

$dstServerTip = "To enable your server, you must \r
1. Enable user apache to login to the destination server with public key. The public key can be found @ /home/apache/.ssh/id_rsa.pub \r
2. Mount the encrypted backups on destination server. \r
3. Copy /usr/local/dba on destination server. \r
4. Get /export/home/oracle10/.ssh/EncryptDBBkupKey.
";

echo "<table border=0 id=\"decryptDBBkupTable\" class=\"centretable\" width=\"100%\">";
echo "<tr>";

echo "<tbody id=\"backupInfo\">";

echo "<td align=\"center\">";

echo "<form id=\"decryptDBBackupsForm\" method=\"POST\">";
echo "<table border=0 id=\"sessionContentTable\">";

echo "<tr>";
echo "<td><userLable>File Set: </userLable></td>";
echo '<td>
<input type="radio" name="fileSet" id="fileSetNewerThan" value="0" checked><label for="fileSetNewerThan">Newer Than</label><br>
<input type="radio" name="fileSet" id="fileSetOne" value="1"><label for="fileSetOne">Single File</label><br>
<input type="radio" name="fileSet" id="fileSetWhole" value="2"><label for="fileSetWhole">Whole Folder</label><br>
';
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td>Encrypted Backup: </td> <td>
<div id=\"encryptedBackupDiv\">
<input type=\"text\" id=\"encryptedBackup\" name=\"encryptedBackup\" size=\"80\" placeholder=\"/full/path/to/source\"/>";
echo "</div>";

echo "<div id=\"decryptionTips\">";

echo "<br><font color=\"#D3D3D3\"> 
&#x2022;If the input is folder, all the files in the folder will be decrypted.
<br>&#x2022;If the input is a file, all the files newer than it will be decrypted
</font>
</div>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>" . "<a id = \"dstServerTip\" href = \"#\" title = \"" . $dstServerTip . "\"> Destination: </a>" . " </td> <td>
<div id=\"selectDstSrvName\">
<select name=\"dstServerName\" id=\"dstServerName\">
  <option value=\"--\" >--Please choose a server--</option>
  <option value=\"lin03\">lin03 (Linux POC)</option>
  <option value=\"sun01\">sun01 (QA/BAT RAC One)</option>
  <option value=\"sun08\">sun08 (QA/BAT RAC One)</option>
  <option value=\"tcsfs03\">tcsfs03</option>
</select>
</div>
";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td>Decrypted Location: </td> <td>
<div id=\"decryptedLocationDiv\">
<input type=\"text\" id=\"decryptedLocation\" name=\"decryptedLocation\"  size=\"80\" placeholder=\"/full/path/to/destination\"/>";
echo "</div>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"right\">";
echo "<div id=\"buttonsSubmitDiv\">";
echo "<input type=\"submit\" name=\"submit\" id=\"runTask\"  value=\"Decrypt DB Backup\"/> ";
echo "</div>";
echo "</td> <td align=\"left\">";
echo "<div id=\"buttonsResetDiv\">";
echo "<input type=\"reset\" name=\"reset\" value=\"Reset\" id=\"resetinputs\" />";
echo "</div>";
echo "</td>";

echo "</tr>";

echo "</table>";
echo "</form>";
echo "</td>";
echo "</tbody>";
echo "</tr>";
echo "</table>";

echo "<table style=\"table-layout:fixed; width:250px\">";
echo "<tr>";
echo "<td align=\"center\">";
echo "<div id=\"jobProgress\" class=\"jobProgress\">";
echo "</div>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"center\">";
echo "<div id=\"jobLogDiv\" class=\"jobLog\">";
echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";

?>
