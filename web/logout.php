<?php
session_start();
$currentWorkingDir = dirname(__FILE__);
$configDir = $currentWorkingDir . "/common";

if (realpath($configDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
}

$sharedFunction = 'sharedFuncs.php';
require ($sharedFunction);

pushToLogFile("AUTH:" . $_SESSION['uid'] . " logged out.");

session_unset();
session_destroy();
session_write_close();
setcookie(session_name(), '', 0, '/');
session_regenerate_id(true);

echo "
<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery-ui/jquery-ui.js\" type=\"text/javascript\"></script>
";

echo '
<script>
$(function(){
  $("#toLogin").click(function(){
     window.location.replace("/login.php");
     return false;
  });
  });
</script>
';

echo "<table border=0 id=\"outLineTable\" class=\"centretable\" width=\"100%\">";
echo "<tr>";
echo "<td align=\"center\">";

echo "You have been logged out.\r\n";
echo "Please click <a href=\"#\" id=\"toLogin\">Here</a> to login.";
echo "</td>";
echo "</tr>";
echo "</table>";

?>
 
