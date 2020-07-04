<?php
session_start();

// Verify if the user logged in
$vSessionScript='/common/verifySession.php';
if (!file_exists($vSessionScript)) {
  $vSessionScript='../common/verifySession.php';
}

require($vSessionScript);

if(isset($_SESSION['fullname']))
   $fullName = $_SESSION['fullname'];

if(isset($_SESSION['mail']))
   $userMail = $_SESSION['mail'];

echo "
<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.validate.min.js\" type=\"text/javascript\"></script>

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

    // Setup form validation on the #changePWDForm element
    $('#changePWDForm').validate({

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

        $(document).on('click', \"#submitChange\", function (e) {
        e.preventDefault();
        //$(\"#messageDiv\").hide();
        var userPWD = $(\"#currentPwd\").val();
        var hasError = false;
        var newPWD = $(\"#newPwd\").val();
        var checkVal = $(\"#checkPwd\").val();

	//var pwdData=$(\":input\").serializeArray();

        if(hasError == true) {return false;}

        if($(\"#changePWDForm\").valid()) {
                formData = [];
                $.each(['userPWD', 'newPWD'], function(index, value) {
                    formData.push({name: value,value: eval(value) });
                });

                $.ajax({
                type: \"POST\",
                url: \"/security/cgiChangeOraPwd.php\",
		dataType : 'html',
                data: formData,
                success: function(data, textStatus, jQxhr ){
		    if ($.trim(data) == 'success') {
                        msg = 'Your password has been changed successfully.';
        		$(\"#oraPWDDiv\").hide();
        		$(\"#messageDiv\").html(msg);
			//$(\"#resetinputs\" ).trigger(\"click\" );
		    } else {
        		$(\"#messageDiv\").html(data);
		    } 
		},
		error: function( jqXhr, textStatus, errorThrown ){
        		$(\"#messageDiv\").html(errorThrown);
                    console.log( errorThrown );
                }
                });
        };
    });
});

//$(document).ready(function () {
//   $(\"#newPwd, #checkPwd\").keyup(checkPasswordMatch);
//});

</script>
";
echo "<table border=0 id=\"embededLoginTable\" class=\"outLineTable\">";
echo "<tr>";
echo "<tbody id=\"oraPWDDiv\">";
echo "<td align=\"center\">";
echo "<form id=\"changePWDForm\" method=\"post\" class=\"ora_form\">";
echo "<table border=0 class=\"centreTable\">";

echo "<tr>";

echo "<td  align=\"left\">";
echo '<requirements>
<li type="square">Minimum 8, and Maximum 30</li>
';

//<li type="square">Include at least one Uppercase and lowercase</li>
echo '
<li type="square">Include at least one number</li>
<li type="square">_$#,&"\\ is not allowed</li>
</requirements>';
echo "</td>";
echo "</tr>";
echo "<tr>";
if ( empty($userId ))
{
	echo "Seems you haven't log in, please contact helpdesk if you need help.";
}
	
echo "</tr>";
echo "<tr>";
echo "<td><userLable>Current Password:</userLable><br/><input type=\"password\" name=\"currentPwd\" id=\"currentPwd\" class=\"ora_pwd\" size=\"30\" /><mandatory>*</mandatory></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>New Password:</userLable><br /><input type=\"password\" name=\"newPwd\" id=\"newPwd\" class=\"ora_pwd\" size=\"30\" /><mandatory>*</mandatory></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>Confirm New Password</userLable><br /><input type=\"password\" name=\"checkPwd\" id=\"checkPwd\" class=\"ora_pwd\" size=\"30\" /><mandatory>*</mandatory></td>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"left\"><input type=\"submit\" name=\"submit\" value=\"Change Password\" id=\"submitChange\" />    <input type=\"reset\" name=\"reset\" value=\"Reset\" id=\"resetinputs\" /></td>";
//echo "<td></td><td><input type=\"reset\" name=\"reset\" value=\"Reset\" id=\"resetinputs\" /></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</td>";
echo "</tbody>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"center\">";
echo "<div id=\"messageDiv\" class=\"mssgDiv\">";
echo "<md></md>";
echo "</div>";
echo "<div id=\"validateMsgDiv\" class=\"mssgDiv\">";
echo "<md></md>";
echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";
?>
 
