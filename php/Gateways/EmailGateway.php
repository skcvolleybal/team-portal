<?php

use PHPMailer\PHPMailer\PHPMailer;

class EmailGateway
{
    function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function QueueEmails($emails)
    {
        if (!$emails) {
            return;
        }
        if (!is_array($emails)) {
            return;
        }

        foreach ($emails as $email) {
            if (!$emails) {
                continue;
            }
            if (!$email->IsValid()) {
                continue;
            }
            if ($this->DoesEmailExist($email)) {
                continue;
            }

            $query = "INSERT INTO teamportal_email (
                        sender_naam,
                        sender_email,
                        receiver_naam,
                        receiver_email,
                        titel,
                        body,
                        signature
                      ) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $params = [
                $email->sender->naam,
                $email->sender->email,
                $email->receiver->naam,
                $email->receiver->email,
                $email->titel,
                $email->body,
                $email->signature
            ];
            $this->database->Execute($query, $params);
            $this->PrintEmail($email);
        }
    }

    public function SendQueuedEmails()
    {
        $query = "SELECT * FROM teamportal_email WHERE send_date is null";
        $emails = $this->database->Execute($query);

        if (count($emails) == 0) {
            echo "Geen emails te verzenden";
            return;
        }

        foreach ($emails as $email) {
            $sender = new Persoon(-1, $email->sender_naam, $email->sender_email);
            $receiver = new Persoon(-1, $email->receiver_naam, $email->receiver_email);
            $newEmail = new Email($email->titel, $email->body, $receiver, $sender);
            if ($this->SendMail($newEmail)) {
                $this->MarkEmailAsSent($email);
            }
        }
    }

    private function DoesEmailExist($email)
    {
        $signature = $email->signature;
        $query = "SELECT id FROM teamportal_email WHERE signature = '$signature'";
        $emails = $this->database->Execute($query);

        return count($emails) > 0;
    }

    private function MarkEmailAsSent($email)
    {
        $query = "UPDATE teamportal_email set send_date = NOW() where id = ?";
        $params = [$email->id];
        $this->database->Execute($query, $params);
    }

    private function SendMail($email)
    {
        if (
            !filter_var($email->sender->email, FILTER_VALIDATE_EMAIL) ||
            !filter_var($email->receiver->email, FILTER_VALIDATE_EMAIL)
        ) {
            echo "Foute email: '" . $email->sender->email . "' of '" . $email->receiver->email . "'<hr>";
            return false;
        }

        $PHPMailer = new PHPMailer();
        $PHPMailer->CharSet = 'UTF-8';
        $PHPMailer->setFrom($email->sender->email, $email->sender->naam);
        $PHPMailer->addAddress($email->receiver->email, $email->receiver->naam);
        $PHPMailer->Subject = $email->titel;
        $PHPMailer->msgHTML($email->body);

        $this->PrintEmail($email);

        if (!$PHPMailer->send()) {
            echo 'Mailer Error: ' . $PHPMailer->ErrorInfo . "<hr>";
            return false;
        }

        return true;
    }

    private function PrintEmail($email)
    {
        echo "From: " . $email->sender->naam . " (" . $email->sender->email .  ")<br>";
        echo "To: " . $email->receiver->naam . " (" . $email->receiver->email . ")<br>";
        echo "<b>" . $email->titel . "</b><br>";
        echo $email->body . "<br><hr>";
    }
}
