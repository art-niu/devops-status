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
// require_once ("sharedFuncs.php");
// require_once ($vSessionScript);
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
 	var editMarkUp = '<button name=\"ok\" onClick=\"callCrudActionDeployment(\'edit\',\'' + collectionName + '\',\'' + id + '\',\'' + fieldName + '\')\">Save</button><button name=\"cancel\" onClick=\"cancelEdit(\'' + id + '\')\">Cancel</button>';
        $(\"#b\"+ id ).html(editMarkUp);
}
    
function cancelEdit(id) {
        $(\"#b\"+ id ).hide();
        $(\"#\"+ id ).prop('readonly','true');
}
    
    
function callCrudActionDeployment(action,collectionName,id,fieldName) {
        $(\"#b\"+ id ).hide();
        //$(\"#WASDEPLOYMENT_\"+ id ).prop('readonly','true');
        $(id).prop('readonly','true');

                    formData = [];
                    formData.push({name: 'id', value: id});

        switch(action) {
                case \"add\":
                        queryString = 'action='+action+'&txtmessage='+ $(\"#txtmessage\").val();
                break;
                case \"edit\":
                    fieldValue=$(\"#\"+ id).val().replace(/\\\/g,\"/\");

                    formData.push({name: 'action', value: action});
                    formData.push({name: 'collection', value: collectionName});
                    formData.push({name: 'field', value: fieldName});
                    formData.push({name: 'value', value: fieldValue});
                    break;
                case \"delete\":
                    formData.push({name: 'action', value: action});
                    formData.push({name: 'collection', value: collectionName});
                break;
        }

        jQuery.ajax({
        url: \"/crud/generalCRUD.php\",
        data: formData,
        type: \"POST\",
        success:function(data){
                switch(action) {
                        case \"add\":
                                $(\"#comment-list-box\").append(data);
                        break;
                        case \"edit\":
                          $(\"#b\"+ id ).hide();
                          $(\"#WASDEPLOYMENT_\"+ id ).prop('readonly','true');
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
            
    // Setup form validation on the #defineWASDepolymentForm element
    $('#defineWASDepolymentForm').validate({
            
        // Specify the validation rules
                //hasUppercase: true,
                //hasLowercase: true,
        rules: {
            binary: {
                required: true
            },
            jvmName: {
                required: true
            },
            applicationUrl: {
                url:true
            }

        },
            
        // Specify the validation error messages
        messages: {
                   binary: 'Please enter the full path to ear file.',
                   jvmName: 'Please enter jvm name.',
                            
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

    $(document).off('click', \"#resetinputs\").on('click', \"#resetinputs\", function (e) {            
            
});

        $(document).off('click', \"#submitDefinition\").on('click', \"#submitDefinition\", function (e) {            
        e.preventDefault();
        var appname = $(\"#appName\").val();
        var envname = $(\"#envName\").val();
        var envid = $(\"#envId\").val();
        var prodline = $(\"#prodLine\").val();
        var stage = $(\"#stage\").val();
        var srvname = $(\"#srvName\").val();
        var jvmname = $(\"#jvmName\").val();
        var binary = $(\"#binary\").val().replace(/\\\/g,\"/\");
        var timeout = $(\"#timeout\").val();
        var applicationurl = $(\"#applicationUrl\").val();
        var description = $(\"#description\").val();
        var deploymentname = prodline + '_' + stage + '_' + jvmname + '_' + appname;
            
                formData = [];
                $.each(['appname', 'envname', 'prodline', 'stage', 'srvname', 'jvmname', 'binary', 'timeout', 'applicationurl', 'description', 'deploymentname'], function(index, value) {
                    formData.push({name: value,value: eval(value) });
                });
            
                $.ajax({
                type: \"POST\",
                url: \"/was/cgiInsertUpdateDeployment.php\",
	        dataType : 'html',
                data: formData,
                success: function(data, textStatus, jQxhr ){
    			if ($.trim(data) == 'success') {
                    msg = 'Deployment has been created/updated successfully.';
        			$(\"#listwasDeployments\").load(\"/was/listDeployments.php\");
        			$(\"#responseDiv\").html(msg);
			        $(\"#resetinputs\" ).trigger(\"click\" );
			} else {
			        $(\"#messageDiv\").html(data);
    			}
		},
			error: function( jqXhr, textStatus, errorThrown ){submitDefinition
		        $(\"#responseDiv\").html(errorThrown);
                	console.log( errorThrown );
                }
                });
    });
});
            
</script>
";

echo "
<script>
$(document).ready(function () {

	$(\"#responseDiv\").html('');

	$(\"#envName\").autocomplete({
            dataType: \"json\",
            minLength: 2,
            source: \"/was/getWASEnvName.php\",
            select: function(event,ui) {
                $(\"#envName\").val(ui.item.value);
                $(\"#envId\").val(ui.item.id);
                $(\"#prodLine\").val(ui.item.prodline);
                $(\"#stage\").val(ui.item.stage);
                $(\"#srvName\").val(ui.item.srvname);
                $(\"#jvmName\").val(ui.item.jvmname);
            }
	});
});
</script>
";
echo "<div id=\"defineWASDeployment\">";
echo "<table border=0 id=\"outlineTable\" class=\"outLineTable\">";
echo "<tr>";
echo "<td align=\"center\">";

echo "<table border=0 id=\"embededLoginTable\" class=\"outLineTable\">";
echo "<tr>";
echo "<tbody id=\"wasEnvDiv\">";
echo "<td align=\"center\">";
echo "<form id=\"defineWASDepolymentForm\" method=\"post\" class=\"ora_form\">";
echo "<table border=0 class=\"centreTable\">";

echo "<tr>";

echo "<td  align=\"left\">";
echo "</td>";
echo "</tr>";
echo "<tr>";
if (empty($fullName)) {
    echo "Seems you haven't log in, please contact helpdesk if you need help.";
}

echo "</tr>";
echo "<tr>";
echo "<td><userLable>Application Name: </userLable><br/><input type=\"text\" name=\"appName\" id=\"appName\" size=\"30\" required /></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>Environment Name: </userLable><br/><input type=\"text\" name=\"envName\" id=\"envName\" size=\"30\" required /></td>";
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

echo "<tr>";
echo "<td><userLable>Server Name: </userLable><br/><input type=\"text\" name=\"srvName\" id=\"srvName\" size=\"40\" required /></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>JVM Name: </userLable><br/><input type=\"text\" name=\"jvmName\" id=\"jvmName\" size=\"30\" required /></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>Full Path to Ear File: </userLable><br/><input type=\"text\" name=\"binary\" id=\"binary\" size=\"30\" required /></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>Deployment Timeout: </userLable><br/><input type=\"text\" name=\"timeout\" id=\"timeout\" size=\"30\" value=\"10\" /></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>Application URL: </userLable><br/><input type=\"text\" name=\"applicationUrl\" id=\"applicationUrl\" size=\"30\" /></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>Description: </userLable><br/><input type=\"text\" name=\"description\" id=\"description\" size=\"30\" required /></td>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"left\"><input type=\"submit\" name=\"submit\" value=\"Submit\" id=\"submitDefinition\" />    <input type=\"reset\" name=\"reset\" value=\"Reset\" id=\"resetinputs\" />";
echo "<input type=\"hidden\" name=\"envId\" id=\"envId\" size=\"30\"/>";
echo "</td>";
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

echo "<td>";

echo "<table>";
echo "<tr>";
echo "<td align=\"center\">";

echo "Existing Deployments";

echo "<div id=\"listwasDeployments\">";

include "eListwasDeployments.php";

echo "</div>";

echo "</td>";
echo "</tr>";

echo "<tr>";
echo "<td align=\"center\">";
echo "Existing Enviornments";
echo "<div id=\"listwasEnvironments\">";

include "eListwasEnvironments.php";

echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";

echo "</td>";
echo "</tr>";
echo "</table>";
echo "</div>";
?>
 
