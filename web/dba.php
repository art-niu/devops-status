<?php
session_start();

$killSessionsURL = "oraoperation/adminSearchUserActiveSessions.php";
$listOracleDBLocksURL = "oraoperation/listOracleDBLocks.php";

$currentWorkingDir = dirname(__FILE__);
$commonDir = $currentWorkingDir . "/common";
$configDir = $currentWorkingDir . "/config";
$vendorDir = $currentWorkingDir . "/vendor";
if (realpath($commonDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir . PATH_SEPARATOR . $vendorDir);
}

// Verify if the user logged in
$vSessionScript = 'verifySession.php';

require ($vSessionScript);

require ("config.php");

    echo '
<script>
$(function(){
  $("#showDevices").click(function(){
     $("#contentDiv").load("/crud/listDevices.php");
     return false;
  });
         
  $("#selfKillSessions").click(function(){
     $("#contentDiv").load("/oraoperation/showUserActiveSessions.php");
     return false;
  });
         
  $("#prodDBBkupEncryption").click(function(){
     window.location = "/index.php?load=oraoperation/listDatabaseEncryptionStatus.php";
     return false;
  });

  $("#prodDBBkupDecryption").click(function(){
     window.location = "/index.php?load=oraoperation/decryptOraDBBackup.php";
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
        
  $("#wasDStatus").click(function(){
     $("#contentDiv").load("/was/wasDeploymentStatus.php");
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
        
  $("#javabuilds").click(function(){
     window.open("http://jenkins01.csd.toronto.ca:8080/");
     return false;
  });
        
  $("#chgLDAPPwd").click(function(){
     window.open("http://web.csd.toronto.ca/myhelpdesk/userldap/menu.php");
     return false;
  });
        
  $("#elcqpStatus").click(function(){
     $("#contentDiv").load("/jobstatus/elcqpNightlyJobStatus.php");
     return false;
  });
        
  $("#tapeBkupStatus").click(function(){
     $("#contentDiv").load("/jobstatus/tapeBackupStatus.php");
     return false;
  });
        
  $("#defineWASEnv").click(function(){
     $("#contentDiv").load("/was/defineWASEnv.php");
     return false;
  });
        
  $("#defineDeployment").click(function(){
     $("#contentDiv").load("/was/defineDeployment.php");
     return false;
  });
        
  $("#logOut").click(function(){
//     $("#contentDiv").load("/logout.php");
//     location.reload();
     window.location.replace("/logout.php");
     return false;
  });
});
</script>
';
    echo '
<table id="oracleOperationTable" class="oracleOperationTable" style="width:100%">
<tr>
<td align="center" bgcolor="#CCEEFF">
Production Database
</td>
<td align="center">
QA Database
</td>
<td align="center" bgcolor="#CCEEFF">
Training Database
</td>
<td align="center">
Development Database
</td>
</tr>

<tr>
<td align="left" bgcolor="#CCEEFF">';
    
    echo '<a href="#" id="killUserSessions" class="menuitem">Production Database User Sessions</a> <br>';
    echo '<a href="#" id="killDBLocks" class="menuitem">Production Database Locks</a>  <br>';
    echo '<a href="#" id="prodDBBkupEncryption" class="menuitem">Production Database Backup Encryption</a>  <br>';
    echo '<a href="#" id="prodDBBkupDecryption" class="menuitem">Production Database Backup Decryption</a>  <br>';
    
    echo '</td>
<td align="left"> ';
    echo '<a href="#" id="killQAUserSessions" class="menuitem">QA Database User Sessions</a> <br>';
    echo '<a href="#" id="killQADBLocks" class="menuitem">QA Database Locks</a>  <br>';
    
    echo '
</td>
<td align="center" bgcolor="#CCEEFF">
</td>
<td align="center">
</td>
</tr>
<tr>
<td align="center" bgcolor="#CCEEFF">';

$_GET['db'] = 'prod';
include 'oraoperation/eGetOraDatabaseStatus.php';

echo '
    
</td>
<td align="center"> ';

$_GET['db'] = 'qa';
include 'oraoperation/eGetOraDatabaseStatus.php';

echo '
</td>
<td align="center" bgcolor="#CCEEFF">

</td>
<td align="center">

</td>
</tr>
<tr>
<td align="center" bgcolor="#CCEEFF">';

$_GET['db'] = 'prod';
include 'oraoperation/eGetOraASMStatus.php';

echo '
    
</td>
<td align="center"> ';

$_GET['db'] = 'qa';
include 'oraoperation/eGetOraASMStatus.php';

echo '
</td>
<td align="center" bgcolor="#CCEEFF">

</td>
<td align="center">

</td>
</tr>

</table>
';
    ?>
