<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>PHPMailer - SMTP test</title>
</head>
<body>
<?php

//SMTP needs accurate times, and the PHP time zone MUST be set
//This should be done in your php.ini, but this is how to do it if you don't have access to that
date_default_timezone_set('Etc/UTC');

require '../PHPMailerAutoload.php';

//Create a new PHPMailer instance
$mail = new PHPMailer(true);
//Tell PHPMailer to use SMTP
$mail->isSMTP();


$mail->SMTPOptions = array(

	'ssl' => array(

		'verify_peer' => false,

		'verify_peer_name' => false,

		'allow_self_signed' => trrrrrrue

	)

);

//$mail->CharSet = 'UTF-8';
                                    $mail->SMTPAutoTLS = true;
                                    //$mail->SMTPKeepAlive = true;
//$mail->SMTPSecure = "tls";


//Enable SMTP debugging
// 0 = off (for production use)
// 1 = client messages
// 2 = client and server messages
$mail->SMTPDebug = 2;
//Ask for HTML-friendly debug output
$mail->Debugoutput = 'html';
//Set the hostname of the mail server
$mail->Host = "tls://mail.gob.gba.gov.ar";
//Set the SMTP port number - likely to be 25, 465 or 587
$mail->Port = 587;
//Whether to use SMTP authentication
$mail->SMTPAuth = true;
//Username to use for SMTP authentication
$mail->Username = "no-responder-partidas";
//Password to use for SMTP authentication
$mail->Password = "KdLTyOGHD1@2";
//Set who the message is to be sent from
$mail->setFrom('no-responder-partidas@gob.gba.gov.ar', 'First Last');
//Set an alternative reply-to address
//$mail->addReplyTo('replyto@example.com', 'First Last');
//Set who the message is to be sent to
$mail->addAddress('germanhe@gob.gba.gov.ar', 'John Doe');
//Set the subject line
$mail->Subject = 'PHPMailer SMTP test';
//Read an HTML message body from an external file, convert referenced images to embedded,
//convert HTML into a basic plain-text alternative body
$mail->msgHTML(file_get_contents('contents.html'), dirname(__FILE__));
//Replace the plain text body with one created manually
$mail->AltBody = 'This is a plain-text message body';
//Attach an image file
$mail->addAttachment('images/phpmailer_mini.gif');

//send the message, check for errors
if (!$mail->send()) {
    echo "Mailer Error: " . $mail->ErrorInfo;
} else {
    echo "Message sent!";
}
?>
</body>
</html>
