<?php
session_start();
$_SESSION['url'] = $_SERVER['REQUEST_URI'];

$userID = "";
 
if(isset($_SESSION['uid']))
   $userId = $_SESSION['uid']; // holds url for last page visited.

echo "<html>";
echo "<head>";
echo '<link href="../css/centerTables.css" type="text/css" rel="stylesheet" />';
echo "<script src=\"../jquery/jquery-3.1.1.js\" type=\"text/javascript\"></script>";
echo "
<script>
function checkPasswordMatch() {
    var password = $(\"#newPwd\").val();
    var confirmPassword = $(\"#checkPwd\").val();

    if (password != confirmPassword)
        $(\"#messageDiv\").html(\"Passwords do not match!\");
    else
        $(\"#messageDiv\").html(\"Passwords match.\");
};

$(function(){
$(\".resetinputs\").click(function() {
    //$(this).closest('form').find(\"input[type=password], text\").val(\"\");
    $(this).closest('form').find(\"input[type=password]\").val(\"\");
});
        //$(\"#submitChange\").click(function(e){
        $(\"#changePWDForm\").on('submit', function (e) {
        e.preventDefault();
        //$(\"#messageDiv\").hide();
        var userName = $(\"#userName\").val();
        var hasError = false;
        var newpass = $(\"#newPwd\").val();
        var checkVal = $(\"#checkPwd\").val();
        if (newpass == '') {
            $(\"#password\").after('<span class=\"error\">Please enter a password.</span>');
            hasError = true;
        } else if (checkVal == '') {
            $(\"#checkPwd\").after('<span class=\"error\">Please re-enter your password.</span>');
            hasError = true;
        } else if (newpass != checkVal ) {
            $(\"#checkPwd\").after('<span class=\"error\">Passwords do not match.</span>');
            hasError = true;
        }

        if(hasError == true) {return false;}

        if(hasError == false) {
                $.ajax({
                dataType: \"html\",
                type: \"POST\",
                url: \"/security/cgiChangeOraPwd.php\",
                data: $(this).serialize(),
                success: function(data, textStatus, jQxhr ){
		    if ($.trim(data) == 'success') {
                        //msg = userName.contact(\" Your password has been changed successfully.\");
                        msg = userName + '\'s password has been changed successfully.';
        		$(\"#messageDiv\").html(msg);
			$(\"#resetinputs\" ).trigger(\"click\" );
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
   $(\"#newPwd, #checkPwd\").keyup(checkPasswordMatch);
});

</script>
";
echo "</head>";
echo "<body background=\"../images/morainewater02.jpg\">"; 
echo "<table border=0 id=\"centreTableContainer\" class=\"centreTableContainer\">";
echo "<tr>";
echo "<td>";
echo "<table border=0 id=\"centretable\" class=\"centretable\">";
echo "<tr>";
//echo "<form action=\"ldapAuthenticating.php\" method=\"post\">";
echo "<form id=\"changePWDForm\" method=\"post\">";

echo "<tr>";
if ( ! empty($userID ))
	echo "<td ><userLable>Oracle User ID: </userLable></td> <td><input type=\"text\" id=\"userName\" name=\"userName\" value=\"" . $userId . "\"/></td>";
else
	echo "<td><userLable>Oracle User ID: </userLable></td> <td><input type=\"text\" id=\"userName\" name=\"userName\" value=\"\"/></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable><userLable>Current Password:</userLable></td><td><input type=\"password\" name=\"userPWD\" id=\"currentPwd\" /></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>New Password:</userLable></td><td><input type=\"password\" name=\"newPWD\" id=\"newPwd\"  /></td>";
echo "</tr>";
echo "<tr>";
echo "<td><userLable>Confirm New Password:</userLable></td><td><input type=\"password\" name=\"checkPWD\" id=\"checkPwd\"/></td>";
echo "</tr>";
echo "<tr>";
echo "<td align=\"right\"><input type=\"submit\" name=\"submit\" value=\"Change Password\" id=\"submitChange\" /></td><td align=\"left\"><input type=\"reset\" name=\"reset\" value=\"Reset\" id=\"resetinputs\" /></td>";
//echo "<td></td><td><input type=\"reset\" name=\"reset\" value=\"Reset\" id=\"resetinputs\" /></td>";
echo "</tr>";
echo "</form>";
echo "</table>";
echo "</td>";
echo "</tr>";
echo "<tr>";
echo "<td>";
echo "<div id=\"messageDiv\" class=\"mssgDiv\">";
echo "<md></md>";
echo "</div>";
echo "</td>";
echo "</tr>";
echo "</table>";
echo "</body>";
echo "</html>";
?>
 
