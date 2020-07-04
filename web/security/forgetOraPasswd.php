<?php
session_start();

$currentWorkingDir=dirname(dirname(__FILE__));
$commonDir=$currentWorkingDir . "/common";
if (realpath($commonDir)) {
  set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir);
}
// Verify if the user logged in
$vSessionScript='verifySession.php';

require($vSessionScript);

if ($_SERVER['REQUEST_METHOD'] === 'GET')
    {
       if(isset($_GET['cCode'])) 
         $cCode=$_GET['cCode'];
    }

echo "
<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.validate.min.js\" type=\"text/javascript\"></script>

<script>
$(function(){
    // Setup form validation on the #resetPWDForm element
    $('#resetPWDForm').validate({

        // Specify the validation rules
                //hasUppercase: true,
                //hasLowercase: true,
        rules: {
            confirmationCode: {
                required: true
            },
            newPwd: {
                required: true,
                startWithAlphabet: true,
                hasNumber: true,
                hasNoOracleSpecialChar: true,
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
                            confirmationCode: 'Please enter your confirmation code',
                            newPwd: {
                                required: 'Please enter your new password',
                                hasSpecialChar: '_$#,&\"\\ not allowed',
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
                }, '\\\,\"#\$\_& not allowed');


        $(document).on('click', \"#submitChange\", function (e) {
        	e.preventDefault();
	        $(\"#oraPWDDiv\").hide();
	        $(\"#oraPWDSenario\").hide();
        	$(\"#oraPWDPolicies\").hide();
	        var userName = $(\"#userName\").val();

        	var pwdData=$(\":input\").serializeArray();

                $.ajax({
                	type: \"POST\",
	                url: \"/crud/addConfirmationCode.php\",
        	        dataType : 'html',
	                data: pwdData,
        	        success: function(data, textStatus, jQxhr ){
                	    if ($.trim(data) == 'success') {
                        	msg = \"Confirmation email has been sent to \" + $(\"#userMail\").val();
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

	$(\"#confirmationCode\").focusout(function(e) {
	    	var confirmationCode=$(\"#confirmationCode\").val();

                var verifyCcodeData = [];
                $.each(['confirmationCode'], function(index, value) {
                    verifyCcodeData.push({name: value,value: eval(value) });
                });

                $.ajax({
                type: \"POST\",
                url: \"/crud/verifyConfirmationCode.php\",
                dataType : 'html',
                data: verifyCcodeData,
                success: function(data, textStatus, jQxhr ){
                    if ($.trim(data) == \"VALID\") {
                        msg = \"The confirmation code is valid\";
                        $(\"#messageDiv\").html(msg);
                    } else {
                        msg = \"Confirmation code: \" + confirmationCode + \" is INVALID.\";
                        $(\"#messageDiv\").html(msg);
			//$('#confirmationCode').focus();
                    }
                },
                error: function( jqXhr, textStatus, errorThrown ){
                        $(\"#messageDiv\").html(errorThrown);
                    console.log( errorThrown );
                }
                });
	  });

        $(document).on('click', \"#resetinputs\", function (e) {
		$(\"#confirmationCode\").val(\"\");
		$(\"#newPwd\").val(\"\");
		$(\"#checkPwd\").val(\"\");

	});


	$(document).on('click', \"#radioOption\", function (e) {
		var option = $('input[type=\"radio\"]:checked',\"#radioOption\").val();
		switch (option) {
			case \"Confirm\":
				$(\"#oraPWDDiv\").hide();
        			$(\"#oraPWDPolicies\").show();
				$(\"#oraNewPWDDiv\").show();
				break;
			case \"Request\":
				$(\"#oraNewPWDDiv\").hide();
				$(\"#oraPWDDiv\").show();
		}

	});

        $(document).on('click', \"#resetPassword\", function (e) {
	        e.preventDefault();
        	$(\"#radioOptionDiv\").hide();
	        $(\"#oraPWDDiv\").hide();
        	$(\"#oraNewPWDDiv\").hide();

		var newPWD = $(\"#newPwd\").val();
		//var pwdData=$(\":input\").serializeArray();

        	if($(\"#resetPWDForm\").valid()) {
                	formData = [];
                	$.each(['newPWD'], function(index, value) {
 		                   formData.push({name: value,value: eval(value) });
                	});

                	$.ajax({
	                type: \"POST\",
        	        url: \"/security/resetOraclePasswd.php\",
			dataType : 'html',
	                data: formData,
        	        success: function(data, textStatus, jQxhr ){
				    if ($.trim(data) == 'success') {
	                        msg = \"Your password has been reset successfully.\";
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
        	};
	    });
});

$(document).ready(function () {
//   $(\"#newPwd, #checkPwd\").keyup(checkPasswordMatch);
                var option = $('input[type=\"radio\"]:checked',\"#radioOption\").val();
                switch (option) {
                        case \"Confirm\":
                                $(\"#oraPWDDiv\").hide();
                                $(\"#oraNewPWDDiv\").show();
				$('#confirmationCode').focus();
                                break;
                        case \"Request\":
                                $(\"#oraNewPWDDiv\").hide();
                                $(\"#oraPWDDiv\").show();
                }

});

</script>
";
echo "<table border=0 id=\"embededForgetTable\" class=\"embededForgetTable\" style=\"width:100%\" align=\"center\">";
echo "<tr>";
echo "<tbody id=\"oraPWDSenario\">";
echo "<td class=\"centerTd\">";
echo "<div id=\"radioOptionDiv\">";
echo "<table border=0 align=\"center\">";
echo "<tr>";
echo "<td align=\"left\">"; 
echo ' <form id="radioOption">';

if ( ! empty($cCode)){
  echo '<input type="radio" name="resetpassword" id="request" value="Request">Request to RESET Password <br/>';
  echo '<input type="radio" name="resetpassword" id="confirm" value="Confirm" checked="checked">I have got confirmation code.<br/>';
} else {
  echo '<input type="radio" name="resetpassword" id="request" value="Request" checked="checked">Request to RESET Password <br/>';
  echo '<input type="radio" name="resetpassword" id="confirm" value="Confirm">I have got confirmation code.<br/>';
}
echo ' </form> ';
echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</td>";
echo "</tbody>"; //User Senarios
echo "</tr>";

echo "<tr>";
echo "<tbody id=\"oraPWDDiv\">";
//echo "<td class=\"centerTd\">";
echo "<td align=\"center\">";
echo "<table border=0 id=\"ecentretable\" class=\"ecentretable\">";
echo "<form id=\"changePWDForm\" method=\"post\" class=\"ora_form\">";

echo "<tr>";
if ( empty($userId ))
{
        echo "Seems you haven't log in, please contact helpdesk if you need help.";
}

echo "</tr>";
echo "<tr>";
echo "<td align=\"right\"><input type=\"submit\" name=\"submit\" value=\"I'd like to RESET my password\" id=\"submitChange\" /></td>";
echo "</tr>";
echo "</form>";

echo "</table>";
echo "</td>";
echo "</tbody>";
echo "</tr>";

// Reset Password
echo "<tr>";
echo "<tbody id=\"oraNewPWDDiv\" style=\"display: none;\">";
echo "<td align=\"center\">";
echo "<form id=\"resetPWDForm\" method=\"post\" class=\"ora_form\">";
echo "<table border=0 id=\"ecentretable\" class=\"ecentretable\">";
echo "<tr>";

echo "<td  align=\"left\">";
echo '<requirements>
<li type="square">Minimum 8, and Maximum 30</li>
<li type="square">Include at least one Uppercase and lowercase</li>
<li type="square">Include at least one number</li>
<li type="square">_$#,&"\\ is not allowed</li>
</requirements>';
echo "</td>";
echo "</tr>";

echo "<tr>";
if ( ! empty($cCode))
	echo "<td><userLable><userLable>Confirmation Code:</userLable><br /><input type=\"text\" name=\"confirmationCode\" id=\"confirmationCode\" class=\"ora_pwd\" value=\"" . $cCode . "\"/><mandatory>*</mandatory></td>";
else
	echo "<td><userLable><userLable>Confirmation Code:</userLable><br /><input type=\"text\" name=\"confirmationCode\" id=\"confirmationCode\" class=\"ora_pwd\"/><mandatory>*</mandatory></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>New Password:</userLable><br /><input type=\"password\" name=\"newPwd\" id=\"newPwd\" class=\"ora_pwd\" /><mandatory>*</mandatory></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>Confirm New Password:</userLable><br /><input type=\"password\" name=\"checkPwd\" id=\"checkPwd\" class=\"ora_pwd\"/><mandatory>*</mandatory></td>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"left\"><input type=\"submit\" name=\"submit\" value=\"Reset Password\" id=\"resetPassword\" />    <input type=\"reset\" name=\"reset\" value=\"Clean Up\" id=\"resetinputs\" /></td>";
//echo "<td></td><td><input type=\"reset\" name=\"reset\" value=\"Reset\" id=\"resetinputs\" /></td>";
echo "</tr>";
echo "</table>";
echo "</form>";
echo "</td>";
echo "</tbody>";
echo "</tr>";

// Password Policies
echo "<tr>";
echo "<tbody id=\"oraPWDPolicies\" style=\"display: none;\">";
echo "<td >";
echo "</td>";
echo "</tbody>"; //Password Ploicies
echo "</tr>";

echo "</table>";

echo "<table style=\"width:100%\">";
echo "<tr>";
//echo "<td class=\"centerTd\">";
echo "<td align=\"center\">";
echo "<div id=\"messageDiv\" class=\"mssgDiv\"> </div>";
echo "<div id=\"validateMsgDiv\" class=\"mssgDiv\">";
echo "</td>";
echo "</tr>";
echo "</table>";

?>
 
