<?php

    if (isset ( $_GET ['notification'] )) {
        $contentIndex = $_GET ['notification'] ;
    } else {
        $contentIndex = "";
    }
    
    use PHPMailer\PHPMailer\PHPMailer;
    require_once 'vendor/autoload.php';
    
    $arrContextOptions=array(
        "ssl"=>array(
            "verify_peer"=>false,
            "verify_peer_name"=>false,
        ),
    );
    
    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    $mail->setFrom('aniu@toronto.ca', 'Arthur Niu');
    //Set an alternative reply-to address
    $mail->addReplyTo('aniu@toronto.ca', 'Arthur Niu');
    //Set who the message is to be sent to
    
    $protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

    $siteUrl = $protocol . $_SERVER['HTTP_HOST'];

    $currentWorkingDir=dirname(dirname(__FILE__));
    $commonDir=$currentWorkingDir . "/common";
    $configDir=$currentWorkingDir . "/config";
    if (realpath($commonDir) or realpath($configDir)) {
      set_include_path(get_include_path() . PATH_SEPARATOR . $commonDir . PATH_SEPARATOR . $configDir);
    }
    require("dbconf.php");

    include_once('globalConfig.php');

    $dbName = 'tcsit';

    $todayDate = date ( "Y-m-d" );
    $regex = new \MongoDB\BSON\Regex ( '^' . $todayDate, 'm' );

    if ( empty($contentIndex) || $contentIndex == "" ) {
        $command = new MongoDB\Driver\Command ( [
                'aggregate' => 'subscription' ,
                'pipeline' => [
                                [ '$sort' => [ 'sindex' => -1 ] ]
                ],
                'cursor' => new stdClass ()
        ] );
    } else { 							//if notification index not specified.
        $command = new MongoDB\Driver\Command ( [
                'aggregate' => 'subscription' ,
                'pipeline' => [
                                [ '$match' => ['sindex' => $contentIndex ]], 
                                [ '$sort' => [ 'sindex' => -1 ]]
                ],
                'cursor' => new stdClass ()
        ] );
    }

    try {
       $cursor = $manager->executeCommand ( $dbName, $command );
       $recipients = "";
       foreach ( $cursor as $document => $notification ) {
            $fullUrl = $siteUrl . $notification->contentpage;
            $response = file_get_contents($fullUrl, false, stream_context_create($arrContextOptions));
            $mail->msgHTML($response,'/usr/share/dboard',false);
            
            $mail->Subject = $notification->subject;
            
            foreach ($notification->subscribers as $subscriber) {
               $mail->addAddress($subscriber->email, $subscriber->name);
            }
             
            if (!$mail->send()) {
                echo $notification->subject . " Mailer Error: " . $mail->ErrorInfo . "<br>";
            } else {
                echo $notification->subject . " sent! <br>";
            }
            
       }
    } catch (MongoDB\Driver\Exception\Exception $e) {
        echo $e->getMessage(), "\n";
    }
?>
