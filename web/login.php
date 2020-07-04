<?php
session_start();
//$_SESSION['url'] = $_SERVER['REQUEST_URI'];
 
date_default_timezone_set('EST');

if ( isset($_SERVER['QUERY_STRING'])) {
   $qry = $_SERVER['QUERY_STRING'];
} else {
   $qry = "";
}

$loadPage="welcomeEndUsers.html";
if(isset($_GET['load']))
        $loadPage=$_GET['load'];

if(isset($_SESSION['fullname'])) {
	header("Location: index.php?" . $qry);
	die();
}

// check to see if user is logging out
if(isset($_GET['out'])) {
	// destroy session
	session_unset();
	$_SESSION = array();
	unset($_SESSION['uid'],$_SESSION['fullname']);
	session_destroy();
	header("Location: index.php?" . $qry);
}
 
// check to see if login form has been submitted
if(isset($_POST['userLogin'])){
	// run information through authenticator
	if(authenticate($_POST['userLogin'],$_POST['userPassword']))
	{
		// authentication passed
		header("Location: index.php?" . $qry);
		die();
	} else {
		// authentication failed
		$error = 1;
	}
}
 
    $currentWorkingDir=dirname(__FILE__);
    $configDir=$currentWorkingDir . "/common";

    if (realpath($configDir)) {
      set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
    }

    $sharedFunction='sharedFuncs.php';

    require($sharedFunction);

// output error to user
if(isset($error)) echo "Login failed: Incorrect user name, password, or rights<br /-->";
 
// output logout success
if(isset($_GET['out'])) {
  echo "Logout successful";
}
echo "<html>";
echo "<head>";
echo '<link href="/css/tables.css" type="text/css" rel="stylesheet" />';
echo "</head>";
echo "<body background=\"images/morainewater02.jpg\">"; 
echo "<table border=0 id=\"ltContainer\" class=\"loginTableContainer\">";
echo "<tr>";
echo "<td>";
echo "<table border=0 id=\"logintable\" class=\"logintable\">";
echo "<tr>";
echo "<td>";
echo "<form action=\"ldapAuthenticating.php\" method=\"post\">";

echo "<tr>";
	echo "<td>User: </td> <td><input type=\"text\" name=\"userName\"  autofocus/></td>";
echo "</tr>";
echo "<tr>";
echo "<td>Password:</td><td><input type=\"password\" name=\"userPWD\" /></td>";
echo "</tr>";
echo "<tr>";
echo "<td></td><td><input type=\"submit\" name=\"submit\" value=\"Login\" /></td>";
echo "</tr>";
echo "</form>";
echo "<td>";
echo "<tr>";
echo "</table>";
echo "<td>";
echo "<tr>";
echo "</table>";
echo "</body>";
echo "</html>";
?>
 
