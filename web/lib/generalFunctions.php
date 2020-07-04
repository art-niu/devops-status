<?php
function appLog($string, $keyWord = "DASH") {

        $date = date("m/d/Y h:i:s A");

        global $logPath;
        global $logFile;

        $message = "$date - %%" . $keyWord . " " . $string . "\n";

        $logFile = $logPath . "/" . $logFile;

        $fp = @fopen($logFile, "a");

        if ($fp) {
            @fwrite($fp, $message);
            fclose($fp);
        }

}


?>
