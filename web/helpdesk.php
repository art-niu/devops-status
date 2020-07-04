<?php
session_start();

$killSessionsURL = "oraoperation/adminSearchUserActiveSessions.php";
$listOracleDBLocksURL = "oraoperation/listOracleDBLocks.php";

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
  $("#selfKillSessions").click(function(){
     $("#contentDiv").load("/oraoperation/showUserActiveSessions.php");
     return false;
  });
         
         
  $("#killUserSessions").click(function(){
    window.location = "/index.php?load=' . $killSessionsURL . '&db=prod";';
    
    echo '
     return false;
  });
        
  $("#killQAUserSessions").click(function(){
    window.location = "/index.php?load=' . $killSessionsURL . '&db=qa";';
    
    echo '
     return false;
  });
        
        
  $("#killDBLocks").click(function(){
      window.location = "/index.php?load=oraoperation/listOracleDBLocks.php&db=prod";';
    
    echo '
     return false;
  });
        
  $("#killQADBLocks").click(function(){
      window.location = "/index.php?load=oraoperation/listOracleDBLocks.php&db=qa";';
    
    
    //     $("#contentDiv").load("' . $listOracleDBLocksURL . '", {db:"qa"}); ';
    
    // header('Location: index.php?resp=0#contact');
    
    echo '
     return false;
  });
        
  $("#resetOraPwd").click(function(){
     $("#contentDiv").load("/security/forgetOraPasswd.php");
     return false;
  });
        
  $("#changeOraPwd").click(function(){
     $("#contentDiv").load("/security/eUiChangeOraPwd.php");
     return false;
  });
        
});
</script>
';
    echo '
<table id="oracleOperationTable" class="oracleOperationTable" style="width:100%">
<tr bgcolor="#CCEEFF">
<td align="center">
Production Database
</td>
<td align="center">
QA Database
</td>
<td align="center">
Training Database
</td>
<td align="center">
Development Database
</td>
</tr>
<tr>
<td align="left">';
    
    echo '<a href="#" id="killUserSessions" class="menuitem">Production Database User Sessions</a> <br>';
    echo '<a href="#" id="killDBLocks" class="menuitem">Production Database Locks</a>  <br>';
    
    echo '</td>
<td align="left"> ';
    echo '<a href="#" id="killQAUserSessions" class="menuitem">QA Database User Sessions</a> <br>';
    echo '<a href="#" id="killQADBLocks" class="menuitem">QA Database Locks</a>  <br>';
    
    echo '
</td>
<td align="center">
</td>
<td align="center">
</td>
</tr>
</table>
';

