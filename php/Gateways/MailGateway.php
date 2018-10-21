<?php
use PHPMailer\PHPMailer\PHPMailer;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

class MailGateway
{
    static $number = 0;
    public function SendMail($fromAddress, $fromName, $toAddress, $toName, $title, $body)
    {
        if (!filter_var($toAddress, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $PHPMailer = new PHPMailer();
        $PHPMailer->CharSet = "UTF-8";
        $PHPMailer->setFrom($fromAddress, $fromName);
        $PHPMailer->addAddress($toAddress, $toName);
        $PHPMailer->Subject = $title;
        $PHPMailer->msgHTML($body);

        if (!$PHPMailer->send()) {
            echo "Mailer Error: " . $PHPMailer->ErrorInfo;
        }
        //echo $fromAddress . "<br>" . $fromName . "<br>" . $toAddress . "<br>" . $toName . "<br>" . $title . "<br>" . $body;
    }
}
