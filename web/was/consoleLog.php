<?php

// While not reached the end of the file
// /devZone/logs/secure9dev/deployment
echo "
<style>
#consoleLogDiv {
    position:absolute;
    left:0;
    right:0;
    border:1px solid #000;
 
    overflow:auto;
    top:0;
    bottom:3em;
}
</style>
";

echo "
<script src=\"/jquery/jquery-3.1.1.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.validate.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery-ui/jquery-ui.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.min.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.tablesorter.pager.js\" type=\"text/javascript\"></script>
<script src=\"/jquery/jquery.serializejson.js\" type=\"text/javascript\"></script>
";

echo "

<script>
$(document).ready(function(){
    $('#consoleLogDiv').animate({
        scrollTop: $('#consoleLogDiv')[0].scrollHeight}, 2000);
});
</script>
";

$logFile = "/devZone/logs/secure9train/deployment/CSIS_server1_ws9train.log";

$fp = fopen($logFile, 'r');

echo '<div id="consoleLogDiv">';
//while (! feof($fp)) {
while ($line = fgets($fp,1000)) {
    print $line . '<br>';
    
    // Flush the output cache
    ob_flush();
}

echo '</div>';
?>
