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
<table id="saOperationTable" class="saOperationTable" style="width:100%">

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

<tr>
<td>
<h2>WebSphere</h2>
</td>
</tr>

<tr>
<td>
';

  include 'websphere.php';   
    
echo '    
</td>
</tr>

<tr>
<td>
<h2>Database</h2>
</td>
</tr>

<tr>
<td>
';

 include 'dba.php';   
    
echo '    
</td>
</tr>

<tr>
<td>
<h2>Application Task</h2>
</td>
</tr>

<tr>
<td>
';

  include 'ba.php';   
    
echo '    
</td>
</tr>

</table>
';
?>
