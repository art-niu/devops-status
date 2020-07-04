<?php
session_start();

$killSessionsURL = "/oraoperation/adminSearchUserActiveSessions.php";
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

/*
if (isset($_GET['load']))
    $loadPage = $_GET['load'];
*/

if (isset($_SESSION['mypage']))
    $bannerLoadPage = $_SESSION['mypage'];
else 
    $bannerLoadPage = 'welcomeEndUsers.html';
        
echo '
<script>
$(function(){
  $("#home,#dashBoard").click(function(){
     $("#contentDiv").load("/' . $bannerLoadPage . '");
     return false;
  });

  $("#wasDStatusBanner").click(function(){
     window.location = "/index.php?load=was/wasDeploymentStatus.php";
     return false;
  });
        
  $("#systemStatus").click(function(){
     window.location = "/index.php?load=systemStatus.php";
     return false;
  });

  $("#showDevices").click(function(){
     window.location = "/index.php?load=crud/listDevices.php";
     return false;
  });

  $("#selfKillSessions").click(function(){
     window.location = "/index.php?load=oraoperation/showUserActiveSessions.php";
     return false;
  });
';

echo '
  $("#resetOraPwd").click(function(){
     $("#contentDiv").load("/security/forgetOraPasswd.php");
     return false;
  });

  $("#changeOraPwd").click(function(){
     $("#contentDiv").load("/security/eUiChangeOraPwd.php");
     return false;
  });

  $("#javabuilds").click(function(){
     window.open("http://jenkins.csd.toronto.ca/");
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

  $("#defineDeployment").click(function(){
     window.location = "/index.php?load=was/defineDeployment.php";
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
<table id="bannerTable" class="bannerTable" style="width:100%">
<tr>
<td align="left">
<h1>
Dynamics of IT
</h1>
</td>
<td>
<table  style="width:100%">
<tr>';

echo '<td align="right">';
echo '<div class="dropdown">
  <button class="dropbtn">' . $fullName . '</button>
  <div class="dropdown-content">
';

foreach ($_SESSION['roles'] as &$role) {
    echo $role . "<br>";
}

echo '
    <a href="#" id="logOut">Logout</a>
  </div>
</div>';
echo '</td>';

echo '<tr><td align="right">';

switch ($_SESSION['roles'][0]) {
    case 'infrastructure':
        echo '<a href="#" id="wasDStatusBanner" class="menuitem" class="menuitem">WebSphere Application Deployment Stataus</a> | <a href="#" id="showDevices" class="menuitem">Devices</a> | <a href="#" id="javabuilds" class="menuitem">Java Builds</a> | <a href="#" id="elcqpStatus" class="menuitem" class="menuitem">ELCQP Stataus</a> | <a href="#" id="tapeBkupStatus" class="menuitem" class="menuitem">Tape Backup Stataus</a> ';
        break;
    case 'helpdesk':
        echo '<a href="#" id="showDevices" class="menuitem">Devices</a> ';
        break;
    case 'developer':
        echo '<a href="#" id="home" class="menuitem">Home</a> | <a href="#" id="wasDStatusBanner" class="menuitem" class="menuitem">auto-Deployment Stataus</a> ' . 
        '| <a href="#" id="javabuilds" class="menuitem">Java Builds</a> ' . 
        '| <a href="#" id="elcqpStatus" class="menuitem" class="menuitem">ELCQP Stataus</a> ' .
        '| <a href="#" id="chgLDAPPwd" class="menuitem">Change LDAP Password</a>';
        break;
//     default:
//         echo '<a href="#" id="elcqpStatus" class="menuitem" class="menuitem">ELCQP Stataus</a> ';
//         break;
}

echo '</td></tr>';
echo '
</tr>
</table>
</td>
</tr>
<tr>
<td align="left">
';

//echo '<a href="#" id="home" class="menuitem">Home</a> | <a href="#" id="killQAUserSessions" class="menuitem">QA CSIS II</a> | <a href="#" id="killUserSessions" class="menuitem">Production CSIS II</a> | <a href="#" id="changeOraPwd" class="menuitem">Change Oracle Password</a> | <a href="#" id="chgLDAPPwd" class="menuitem">Change LDAP Password</a> | <a href="#" id="defineWASEnv" class="menuitem">Define WAS Environment</a> | <a href="#" id="defineDeployment" class="menuitem">Define Deployment</a>';
switch ($_SESSION['roles'][0]) {
    case 'infrastructure':
        echo '<a href="#" id="home" class="menuitem">Home</a> | <a href="#" id="systemStatus" class="menuitem"> System Status </a> | <a href="#" id="changeOraPwd" class="menuitem">Change Oracle Password</a> | <a href="#" id="chgLDAPPwd" class="menuitem">Change LDAP Password</a>';
        break;
    case 'helpdesk':
        echo '<a href="#" id="home" class="menuitem">Home</a> | <a href="#" id="systemStatus" class="menuitem"> System Status </a> | <a href="#" id="changeOraPwd" class="menuitem">Change Oracle Password</a> | <a href="#" id="chgLDAPPwd" class="menuitem">Change LDAP Password</a>';
        break;
//     case 'developer':
//         echo '<a href="#" id="home" class="menuitem">Home</a> | <a href="#" id="chgLDAPPwd" class="menuitem">Change LDAP Password</a>';
//         break;
}


// if (in_array('infrastructure', $_SESSION['roles']) || in_array('helpdesk', $_SESSION['roles']) )
//     echo '<a href="#" id="home" class="menuitem">Home</a> | <a href="#" id="systemStatus" class="menuitem"> System Status </a> | <a href="#" id="changeOraPwd" class="menuitem">Change Oracle Password</a> | <a href="#" id="chgLDAPPwd" class="menuitem">Change LDAP Password</a>';

// else
//     echo '<a href="#" id="home" class="menuitem">Home</a> | <a href="#" id="selfKillQASessions" class="menuitem">QA CSIS II Sessions</a> | <a href="#" id="selfKillProdSessions" class="menuitem">Production CSIS II Sessions</a> | <a href="#" id="changeOraPwd" class="menuitem">Change Oracle Password</a> | <a href="#" id="chgLDAPPwd" class="menuitem">Change LDAP Password</a>';

echo '
</td>
</tr>
</table>
';
?>
