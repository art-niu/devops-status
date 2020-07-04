<?php
//session_start();

$currentWorkingDir = dirname(__FILE__);
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
}

// Verify if the user logged in
$vSessionScript = 'verifySession.php';

require ($vSessionScript);

require ("config.php");

    echo '
<script>
$(function(){

  $("#elcqpTask").click(function(){
     window.location = "/index.php?load=was/uiRunElcqpUpdates.php";
     return false;
  });
        
});
</script>
';
    echo '
<table id="appOperationTable" class="appOperationTable" style="width:100%">

<tr>
<td align="left">';
    
    echo '<a href="#" id="elcqpTask" class="menuitem">ELCQP Updates</a> <br>';
    echo '
</td>
</tr>

<tr>
<td align="left">';
    
    
    echo '</td>
<td align="left"> ';
    
    echo '
</td>
<td align="center">
</td>
<td align="center">
</td>
</tr>
</table>
';
    ?>
