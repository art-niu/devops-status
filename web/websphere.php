<?php
session_start();

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

  $("#wasDStatus").click(function(){
     window.location = "/index.php?load=was/wasDeploymentStatus.php";
     return false;
  });
        
  $("#defineWASEnv").click(function(){
     window.location = "/index.php?load=was/defineWASEnv.php";
     return false;
  });
        
  $("#defineDeployment").click(function(){
     window.location = "/index.php?load=was/defineDeployment.php";
     return false;
  });

});
</script>
';
    echo '
<table id="wasOperationTable" class="wasOperationTable" style="width:100%">

<tr>
<td align="left">';
    
    echo '<a href="#" id="defineWASEnv" class="menuitem">Define WAS Environment</a> <br>';
    echo '<a href="#" id="defineDeployment" class="menuitem">Define Deployment</a> <br>';
    echo '
</td>
</tr>

<tr bgcolor="#CCEEFF">
<td align="center">
Production WebSphere Application Servers
</td>
<td align="center">
QA WebSphere Application Servers
</td>
<td align="center">
Training WebSphere Application Servers
</td>
<td align="center">
Development WebSphere Application Servers
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
