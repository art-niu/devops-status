<?php
session_start();

$requiredRoles = array("helpdesk", "dba", "infrastructure");

$currentWorkingDir = dirname(dirname(__FILE__));
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}
$vSessionScript = 'verifySession.php';

require ($vSessionScript);

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

        $(document).off('click', \"#decryptEncryptedBackup2SpecifiedLocation\").on('click', \"#decryptEncryptedBackup2SpecifiedLocation\", function (e) {
        	e.preventDefault();
	        //$(\"#backupInfo\").hide();

        	//var pwdData=$(\":input\").serializeArray();
                var encryptedBackup=$(\"#encryptedBackup\").val();
                var dstServerName=$(\"#dstServerName\").val();
                var decryptedLocation=$(\"#decryptedLocation\").val();
                if($(\"#decryptDBBackupsForm\").valid()) {
                    formData = [];
                    $.each(['encryptedBackup', 'dstServerName', 'decryptedLocation'], function(index, value) {
                        formData.push({name: value,value: eval(value) });
                    });

        	        $.ajax({
                	   type: \"POST\",
	                   url: \"/oraoperation/decryptFolderOrFile.php\",
        	           dataType : 'html',
                	   data: formData,
	                   success: function(data, textStatus, jQxhr ){
                        	$(\"#messageDiv\").html(data);
				        $(\"#backupInfo\").show();
        	           },
                	   error: function( jqXhr, textStatus, errorThrown ){
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
echo "<td>Encrypted Backup: </td> <td>
<div id=\"encryptedBackupDiv\">
<input type=\"text\" id=\"encryptedBackup\" name=\"encryptedBackup\" size=\"80\"/>";
echo "</div>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>" . "<a id = \"dstServerTip\" href = \"#\" title = \"" . $dstServerTip . "\"> Destination: </a>" . " </td> <td>
<div id=\"selectDstSrvName\">
<select name=\"dstServerName\" id=\"dstServerName\">
  <option value=\"--\" >--Please choose a server--</option>
  <option value=\"sun01\">sun01</option>
  <option value=\"tcsfs03\">tcsfs03</option>
</select>
</div>
";
echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td>Decrypted Location: </td> <td>
<div id=\"decryptedLocationDiv\">
<input type=\"text\" id=\"decryptedLocation\" name=\"decryptedLocation\"  size=\"80\"/>";
echo "</div>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"center\">";
echo "<input type=\"submit\" id=\"decryptEncryptedBackup2SpecifiedLocation\" name=\"decryptEncryptedBackup2SpecifiedLocation\" value=\"Decrypt DB Backup\"/>" . "<input type=\"reset\" name=\"reset\" value=\"Reset\" id=\"resetinputs\" />";
echo "</td>";
echo "</tr>";

echo "</table>";
echo "</form>";
echo "</td>";
echo "</tbody>";
echo "</tr>";

echo "<tr>";
echo "<td align=\"center\">";
echo "<div id=\"messageDiv\" class=\"mssgDiv\" align=\"left\">";
echo "<md></md>";
echo "</div>";

echo "<div id=\"validateMsgDiv\" class=\"mssgDiv\">";
echo "<md></md>";
echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";

?>
