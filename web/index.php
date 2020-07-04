<?php /** @noinspection ALL */
//session_start();

$currentWorkingDir = dirname(__FILE__);
$configDir = $currentWorkingDir . "/config";

if (realpath($configDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
}

$globalConfFile = 'globalConfig.php';

require ($globalConfFile);

require 'common/verifySession.php';

$loadPage = 'welcomeEndUsers.html';
if (isset($_GET['load']))
    $loadPage = $_GET['load'];
else
    $loadPage = $_SESSION['mypage'];

$qry = $_SERVER['QUERY_STRING'];

error_log( print_r($qry, TRUE) );

if (isset($_SESSION['fullname']))
    $fullName = $_SESSION['fullname'];
else {
    header("Location: login.php?" . $qry);
    die();
}

if (isset($_SESSION['uid']))
    $userId = $_SESSION['uid'];

if (isset($_SESSION['mail']))
    $userMail = $_SESSION['mail'];

require 'config/dbconf.php';

echo ' <!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<!-- <meta http-equiv="refresh" content="30" /> -->
<title>IT of Toronto Children\'s Services</title>
';

echo "
<link rel=\"stylesheet\" href=\"/css/tablesorter.css\" type=\"text/css\" media=\"print, projection, screen\" />
<link rel=\"stylesheet\" href=\"/css/jquery.tablesorter.pager.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"/jquery-ui/jquery-ui.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"/css/tables.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"/css/centerTables.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"/css/userinfo.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"/css/menu.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"/css/autocomplete.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"/css/bodylayout.css\" type=\"text/css\" />
<link rel=\"stylesheet\" href=\"/css/status.css\" type=\"text/css\" />
";

echo "
<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.validate.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery-ui/jquery-ui.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/parser-network.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.pager.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.serializejson.js\" type=\"text/javascript\"></script>
";

echo '
<script>
    $(document).ready(function () {
        var checkupInterval = 5 * 60 * 1000; // Check session status every 5 minutes
        var sessionURL = "/common/sessionExpire.php";
        var _redirectUrl = "/logout.php";

        var _redirectHandle = null;

        function checkSessionStatus() {
          if (_redirectHandle) clearInterval(_redirectHandle);

          var formData = "";

            _redirectHandle = setInterval(function () {
          $.ajax({
                type: "GET",
                url: sessionURL,
                dataType : "html",
                data: formData,
                success: function(data, textStatus, jQxhr ){
                    if ($.trim(data) == "-1") {
                        msg = "Your session timed out.";
                        clearInterval(_redirectHandle);
                        window.location.href = _redirectUrl;
                    } 
                }
          });

            }, checkupInterval);
        }

        $.ajaxSetup({ complete: function () { checkSessionStatus(); } }); // reset idle redirect when an AJAX request completes

        checkSessionStatus(); // start idle redirect timer initially.
    });
</script>
';

echo '
</head>

<body>
<center>
<table border="0" width="100%">
<tr>
<td>
<div id="headerDiv">';

include "banner.php";

echo '
</div>
</td>
</tr>

<tr>
<td align="center">
<div id="contentDiv">';

include $loadPage;

error_log( print_r(TRUE, $loadPage) );

echo '
</div>
</td>
</tr>
<tr>
<td class="responseTd">
<div id="responseDiv">
</div>
</td>
</tr>

<tr>
<td>
<div id="footDiv">
</div>
</td>
</tr>
</table>
';

echo '
</center>
</body>

</html> 
';
?>

