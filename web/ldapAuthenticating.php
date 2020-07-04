<?php
session_start(); // starts the session

if (isset($_SERVER['HTTP_REFERER'])) {
    $preUrl = $_SERVER['HTTP_REFERER'];
} else {
    $preUrl = "";
}

list ($url, $paras) = explode('?', $preUrl);
// echo $paras;

include 'config/config.php';
// include_once('lib/generalFunctions.php');
// using ldap bind

$currentWorkingDir = dirname(__FILE__);
$configDir = $currentWorkingDir . "/common";

if (realpath($configDir)) {
    set_include_path(get_include_path() . PATH_SEPARATOR . $configDir);
}

$sharedFunction = 'sharedFuncs.php';

require ($sharedFunction);

$uID = $_POST['userName'];
$uPWD = $_POST['userPWD'];

$ldapSrv = 'identldaps.toronto.ca';
$ldapVersion = 3;
$encryption = 'ldaps';
$ldapPort = "636";

$staffBindDN = 'cn=' . $uID . ',ou=Staff,o=toronto';
$nonStaffBindDN = 'cn=' . $uID . ',ou=Non-Staff,o=toronto';
$bindDN = $staffBindDN ;

$srchBase = "o=Toronto";
$srchFilter = "(&(uid=<username>)(objectClass=cotPerson))";
$srchFilter = str_replace("<username>", $uID, $srchFilter);

$output = array();

// connect to ldap server

$ldapString = $encryption . "://" . $ldapSrv . ":" . $ldapPort;

$ldapConn = @ldap_connect($ldapString) or die("Could not connect to LDAP server.");

//var_dump($ldapConn);

if ($ldapConn) {

    if (! @ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, $ldapVersion)) {
        $output = "Protocol Error, Unable to set version";
        @ldap_close($ldapConn);
        return $output;
    }

    /*
     * If Bind DN and Bind Password are required.
     * if (ldap_bind($ldapConn, $bindDN, $bindPWD)) {
     * If Bind DN and Bind Password are required.
     * if (ldap_bind($ldapConn)) {
     */
    $ldapbind = ldap_bind($ldapConn, $staffBindDN, $uPWD);

    if (! $ldapbind) {
        $ldapbind = ldap_bind($ldapConn, $nonStaffBindDN, $uPWD);
        $bindDN = $nonStaffBindDN ;
    }

    if ($ldapbind) {
        /* Search */
        $ldap_results = ldap_search($ldapConn, $srchBase, $srchFilter, array(
            "dn",
            "cn",
            "fullName",
            "uid",
            "mail",
            "telephoneNumber"
        ));
        if ($ldap_results) {
            $ldap_entries = ldap_get_entries($ldapConn, $ldap_results);
            
            error_log( print_r($ldap_entries, TRUE) );
            
            if ($ldap_entries["count"] == "1") {
                /* single response return user dn */
                $output["dn"] = $ldap_entries["0"]["dn"];
                $output["uid"] = $ldap_entries["0"]["uid"][0];
                $output["fn"] = $ldap_entries["0"]["fullname"][0];
                $output["mail"] = $ldap_entries["0"]["mail"][0];
                $output["phone"] = $ldap_entries["0"]["telephonenumber"][0];
                $output["error_num"] = "0";
                $output["error_text"] = "User found";
                pushToLogFile("LDAP_SEARCH: User found, DN '%s'" . $output["dn"]);
            } elseif ($ldap_entries["count"] > 1) {
                /* more than 1 result */
                $output["dn"] = "";
                $output["error_num"] = "13";
                $output["error_text"] = "More than one matching user found";
            } else {
                /* no search results */
                $output["dn"] = "";
                $output["error_num"] = "3";
                $output["error_text"] = "Unable to find users DN";
            }
        } else {
            /* no search results, user not found */
            $output["dn"] = "";
            $output["error_num"] = "3";
            $output["error_text"] = "Unable to find users DN";
        }
    } else {
        /* unable to bind */

        $ldap_error = ldap_errno($ldapConn);
        if ($ldap_error == 0x03) {
            /* protocol error */
            $output["dn"] = "";
            $output["error_num"] = "6";
            $output["error_text"] = "Protocol error";
        } elseif ($ldap_error == 0x31) {
            /* invalid credentials */
            $output["dn"] = "";
            $output["error_num"] = "7";
            $output["error_text"] = "Invalid credentials";
        } elseif ($ldap_error == 0x32) {
            /* insuffient access */
            $output["dn"] = "";
            $output["error_num"] = "8";
            $output["error_text"] = "Insufficient access";
        } elseif ($ldap_error == 0x51) {
            /* unable to connect to server */
            $output["dn"] = "";
            $output["error_num"] = "9";
            $output["error_text"] = "Unable to connect to server";
        } elseif ($ldap_error == 0x55) {
            /* timeout */
            $output["dn"] = "";
            $output["error_num"] = "10";
            $output["error_text"] = "Connection Timeout";
        } else {
            /* general bind error */
            $output["dn"] = "";
            $output["error_num"] = "11";
            $output["error_text"] = "General bind error, LDAP result: " . ldap_error($ldapConn);
        }
        pushToLogFile("LDAP_AUTH:" . $bindDN . "AUTH");
        header("Location: failedLogin.php");
    }

    //In AD, some user ids have capital letters.
    $_SESSION['uid'] = strtolower($output["uid"]);
    $_SESSION['fullname'] = $output["fn"];
    $_SESSION['mail'] = $output["mail"];
    $_SESSION['mypage'] = $output["mypage"];
    setUserRoles();

    header("Location: index.php?" . $paras);
    pushToLogFile("AUTH:" . $uID . " logged in.");
}

?>

