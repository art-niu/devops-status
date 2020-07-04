<?php
session_start();

$currentWorkingDir = dirname(dirname(__FILE__));

$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}

// Verify if the user logged in
$vSessionScript = 'verifySession.php';
$mongoDBConf = 'dbconf.php';

require_once ($mongoDBConf);

require ($vSessionScript);

if (isset($_SESSION['fullname']))
    $fullName = $_SESSION['fullname'];

if (isset($_SESSION['mail']))
    $userMail = $_SESSION['mail'];

echo "<link rel=\"stylesheet\" href=\"../css/tablesorter.css\" type=\"text/css\" media=\"print, projection, screen\" />";
echo "<link rel=\"stylesheet\" href=\"../css/jquery.tablesorter.pager.css\" type=\"text/css\" />";
echo '<link href="/css/buttons.css" type="text/css" rel="stylesheet" />';

echo "
<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.validate.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.serializejson.js\" type=\"text/javascript\"></script>
<script src=\"/jquery-ui/jquery-ui.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.pager.js\" type=\"text/javascript\"></script>
";

echo "
<script>
function showEditBox(editobj,id, fieldName, collectionName) {
		$(editobj).prop('readonly','');
        $(\"#b\"+ id ).show();
        var currentValue = $(id).html();
        var editMarkUp = '<button name=\"ok\" onClick=\"callCrudAction(\'edit\',\'' + collectionName + '\',\'' + id + '\')\">Save</button><button name=\"cancel\" onClick=\"cancelEdit(\'' + id + '\')\">Cancel</button>';
        $(\"#b\"+ id ).html(editMarkUp);
}
            
function cancelEdit(id) {
        $(\"#b\"+ id ).hide();
        $(\"#\"+ id ).prop('readonly','true');
}
            
            
function callCrudAction(action,collectionName,id,fieldName) {
        $(\"#b\"+ id ).hide();
        $(\"#\"+ id ).prop('readonly','true');
        var queryString;
        switch(action) {
                case \"add\":
                        queryString = 'action='+action+'&txtmessage='+ $(\"#txtmessage\").val();
                break;
                case \"edit\":
                        queryString = 'action='+action+'&id='+ id + '&collection=' + collectionName + '&field=' + fieldName + '&value='+ $(\"#\"+ id ).val();
                break;
                case \"delete\":
                        queryString = 'action='+action+'&id='+ id + '&collection=' + collectionName;
                break;
        }
        jQuery.ajax({
        url: \"/crud/generalCRUD.php\",
        data:queryString,
        type: \"POST\",
        success:function(data){
                switch(action) {
                        case \"add\":
                                $(\"#comment-list-box\").append(data);
                        break;
                        case \"edit\":
                          $(\"#b\"+ id ).hide();
                          $(\"#\"+ id ).prop('readonly','true');
                        break;
                        case \"delete\":
                                $(\"#list\" + collectionName).load(\"/was/eList\" + collectionName + \".php\");
                                $('#message_'+id).fadeOut();
                        break;
                }
        },
        error:function (){}
        });
}
</script>
";

echo "
<script>
function ConvertFormToJSON(form){
    var array = jQuery(form).serializeArray();
    var json = {};
            
    jQuery.each(array, function() {
        json[this.name] = this.value || '';
    });
            
    return json;
}
function checkPasswordMatch() {
    var password = $(\"#newPwd\").val();
    var confirmPassword = $(\"#checkPwd\").val();
            
    if (password != confirmPassword)
        $(\"#messageDiv\").html(\"Passwords do not match!\");
    else
        $(\"#messageDiv\").html(\"Passwords match.\");
};
            
$(function(){
            
    // Setup form validation on the #defineWASEnvForm element
    $('#defineWASEnvForm').validate({
            
        // Specify the validation rules
                //hasUppercase: true,
                //hasLowercase: true,
        rules: {
            currentPwd: {
                required: true
            },
            newPwd: {
                required: true,
                startWithAlphabet: true,
                hasNumber: true,
                hasNoOracleSpecialChar: true,
                notEqual: '#currentPwd',
                minlength: 8,
                maxlength: 30
            },
            checkPwd: {
                required: true,
                equalTo: '#newPwd',
                startWithAlphabet: true,
                hasNumber: true,
                minlength: 8,
                maxlength: 30
            }
        },
            
        // Specify the validation error messages
        messages: {
                            currentPwd: 'Please enter your current password',
                            newPwd: {
                                required: 'Please enter your new password',
                                hasNoOracleSpecialChar: '_$#,&\"\\ not allowed',
                                minlength: 'Your password must be at least 8 characters long'
                            },
                            checkPwd: {
                                required: 'Please re-enter your new password',
                                equalTo: 'Your new passwords does NOT match',
                                minlength: 'Your password must be at least 8 characters long'
                            },
        },
    	errorElement : 'div',
    	errorLabelContainer: '#validateMsgDiv'
    });
            
	// Add password validation rules
	$.validator.addMethod('pattern',function(value,element,param){
	    if (this.optional(element)) {
                return true;
        	}
	    if (typeof param === 'string') {
                param = new RegExp('^(?:' + param + ')$');
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
		$(\"#currentPwd\").val(\"\");
		$(\"#newPwd\").val(\"\");
		$(\"#checkPwd\").val(\"\");
            
	});
            
        $(document).off('click', \"#submitDefinition\").on('click', \"#submitDefinition\", function (e) {
        e.preventDefault();
        var envname = $(\"#envName\").val();
        var hasError = false;
        var prodline = $(\"#prodLine\").val();
        var stage = $(\"#stage\").val();
        var srvname = $(\"#srvName\").val();
        var jvmname = $(\"#jvmName\").val();
            
        if(hasError == true) {return false;}
         
        if($(\"#defineWASEnvForm\").valid()) {
                formData = [];
                $.each(['envname', 'prodline', 'stage', 'srvname', 'jvmname'], function(index, value) {
                    formData.push({name: value,value: eval(value) });
                });
            
                $.ajax({
                type: \"POST\",
                url: \"/was/cgiInsertUpdateWASEnv.php\",
        		dataType : 'html',
                data: formData,
                success: function(data, textStatus, jQxhr ){
		    if ($.trim(data) == 'success') {
                        msg = 'WAS Environment has been created/updated successfully.';
        		$(\"#defineWASEnvironment\").load('/was/defineWASEnv.php');
        		$(\"#responseDiv\").html(msg);
			    $(\"#resetinputs\" ).trigger(\"click\" );
		    } else {
        		$(\"#responseDiv\").html(data);
		    }
		},
		error: function( jqXhr, textStatus, errorThrown ){submitDefinition
        		$(\"#responseDiv\").html(errorThrown);
                    console.log( errorThrown );
                }
                });
        };
    });
});
            
</script>
";

echo "
<script>
$(document).ready(function () {
            
    $(\"#jvmName\").change(function () {
        var val = $(this).val();
        var prodline = $(\"#prodLine\").val();
        var stage = $(\"#stage\").val();
        var srvname = $(\"#srvName\").val();
        var jvmname = $(\"#jvmName\").val();

        $(\"#envName\").val(prodline + '_' + stage + '_' + val);
    });
});
            
</script>
";

echo "<div id=\"defineWASEnvironment\">";

echo "<table border=0 id=\"outlineTable\" class=\"outLineTable\">";
echo "<tr>";
echo "<td align=\"center\">";

echo "<table border=0 id=\"formOutlineTable\" class=\"outLineTable\">";
echo "<tr>";
echo "<tbody id=\"wasEnvDiv\">";
echo "<td align=\"center\">";
echo "<form id=\"defineWASEnvForm\" method=\"post\" class=\"ora_form\">";
echo "<table border=0 class=\"centreTable\">";

echo "<tr>";

echo "<td  align=\"left\">";
echo "</td>";
echo "</tr>";
echo "<tr>";
if (empty($fullName)) {
    echo "Seems you haven't logged in, please contact helpdesk if you need help.";
}

echo "</tr>";
echo "<tr>";
echo "<td><userLable>Environment Name:</userLable><br/><input type=\"text\" name=\"envName\" id=\"envName\" size=\"30\" value=\"This will be automatically generated.\" disabled /></td>";
echo "</tr>";
echo "<tr>";
echo '<td><userLable>Production Line: </userLable><br />  <select name="prodLine" id="prodLine">
            <option value="-">-</option>
            <option value="secure">Secure</option>
            <option value="ws">WS</option>
            </select></td>';
echo "</tr>";
echo "<tr>";
echo '<td><userLable>Stage: </userLable><br />  <select name="stage" id="stage">
            <option value="-">-</option>
            <option value="Development">Development</option>
            <option value="QA">QA</option>
            <option value="Training">Training</option>
            </select></td>';
echo "</tr>";
echo '<td><userLable>Server Name: </userLable><br />  <select name="srvName" id="srvName">
            <option value="-">-</option>
            <option value="securedev.csd.toronto.ca">securedev</option>
            <option value="secureqa.csd.toronto.ca">secureqa</option>
            <option value="wsdev.csd.toronto.ca">wsdev</option>
            <option value="wsqa.csd.toronto.ca">wsqa</option>
            <option value="securetrain.csd.toronto.ca">securetrain</option>
            <option value="wstrain.csd.toronto.ca">wstrain</option>
            </select></td>';
echo "</tr>";
echo "<tr>";
echo "<td><userLable>JVM Name:</userLable><br/><input type=\"text\" name=\"jvmName\" id=\"jvmName\" size=\"30\" required /></td>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"left\"><input type=\"submit\" name=\"submit\" value=\"Submit\" id=\"submitDefinition\" />    <input type=\"reset\" name=\"reset\" value=\"Reset\" id=\"resetinputs\" /></td>";
// echo "<td></td><td><input type=\"reset\" name=\"reset\" value=\"Reset\" id=\"resetinputs\" /></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</td>";
echo "</tbody>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"center\">";
echo "<div id=\"messageDiv\" class=\"mssgDiv\">";
echo "</div>";
echo "<div id=\"validateMsgDiv\" class=\"mssgDiv\">";
echo "<md></md>";
echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";

echo "</td>";
echo "<td align=\"center\">";
echo "Existing Enviornments";
echo "<div id=\"listwasEnvironments\">";

include "eListwasEnvironments.php";

echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";
// End of "<div id=\"defineWASEnvironment\">";

?>
 
