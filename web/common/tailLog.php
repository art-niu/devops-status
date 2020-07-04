<?php

$parentDir = dirname(dirname(__FILE__));
$commonDir = $parentDir . "/common";
$configDir = $parentDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}
require ("sharedFuncs.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST')
{
    foreach ($_POST as $key => $value)
        eval("\$$key = \"$value\";");
}
else if ($_SERVER['REQUEST_METHOD'] === 'GET')
{
$logfile = trim($_GET['logfile']);
}

echo "
<!DOCTYPE html>
<!-- Copyright (c) 2012 Daniel Richman; GNU GPL 3 -->
<html>
    <head>
";

echo "<link href=\"/js-logtail-general/logtail.css\" rel=\"stylesheet\" type=\"text/css\">";
echo "<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>";
echo "<script src=\"/js-logtail-general/logtail.js\" type=\"text/javascript\"></script>";


echo "
<script>
  $(function() {

    $(document).ready(function () {

    /* If URL is /logtail/?noreverse display in chronological order */
    var hash = location.search.replace(/^\?/, \"\");
    if (hash == \"noreverse\")
        reverse = false;

    /* Add pause toggle */
/*    $(pausetoggle).click(function (e) {
        pause = !pause;
        $(pausetoggle).text(pause ? \"Unpause\" : \"Pause\");
        show_log();
        e.preventDefault();
    });
*/
     display_log('" . $logfile . "','0');
    });
  });
</script>

";

echo "

    <body>
        <div id=\"header\">
            <a href=\"javascript:display_log('" . $logfile . "','0')\"> Chronological </a> | 
            <a href=\"javascript:display_log('" . $logfile . "','1')\"> Reverse </a> | 
            <a id=\"pause\" href='#'>Pause</a>.
            <input type=\"hidden\" name=\"__logurl\" id=\"__logurl\" size=\"80\" value=\"../logs/tcs_log\"/>
            <input type=\"hidden\" name=\"__logreverse\" id=\"__logreverse\" size=\"1\" value=\"0\"/>

        </div>
    </body>
<pre id=\"data\">Loading...</pre>

</html>

";
?>
