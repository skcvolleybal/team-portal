<?php

namespace TeamPortal\Gateways;

use PHPMailer\PHPMailer\PHPMailer;
use TeamPortal\Common\Database;
use TeamPortal\Entities\Email;
use TeamPortal\Entities\Persoon;
use Exception;

class EmailGateway
{
    function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function QueueEmails(array $emails): void
    {
        $verzondenEmails = 0;

        foreach ($emails as $email) {
            $email->Build();

            if (!$email) {
                continue;
            }
            if (!$email->IsValid()) {
                continue;
            }
            if ($this->DoesEmailExist($email)) {
                continue;
            }

            $verzondenEmails++;

            $query = "INSERT INTO TeamPortal_email (
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
                $email->signature,
            ];
            $this->database->Execute($query, $params);
            $this->PrintEmail($email);
        }

        error_log("Er zijn $verzondenEmails emails gequeued");
    }

    public function SendQueuedEmails(): void
    {
        $query = "SELECT
                    id,
                    sender_email as senderEmail,
                    sender_naam as sender,
                    receiver_email as receiverEmail,
                    receiver_naam as receiver,
                    titel,
                    body
                  FROM TeamPortal_email WHERE send_date is null";
        $rows = $this->database->Execute($query);

        if (count($rows) == 0) {
            error_log("Geen emails te verzenden");
            return;
        }

        foreach ($rows as $row) {
            $newEmail = new Email(
                $row->titel,
                $row->body,
                new Persoon(-1, $row->receiver, $row->receiverEmail),
                new Persoon(-1, $row->sender, $row->senderEmail),
                $row->id,
            );

            if ($this->SendMail($newEmail)) {
                $this->MarkEmailAsSent($newEmail);
            }
        }
    }

    private function DoesEmailExist(Email $email): bool
    {
        $query = "SELECT id FROM TeamPortal_email WHERE signature = ?";
        $params = [$email->signature];
        $emails = $this->database->Execute($query, $params);

        return count($emails) > 0;
    }

    private function MarkEmailAsSent(Email $email): void
    {
        $query = "UPDATE TeamPortal_email set send_date = NOW() where id = ?";
        $params = [$email->id];
        $this->database->Execute($query, $params);
    }

    private function SendMail(Email $email): bool
    {
        if (
            !filter_var($email->sender->email, FILTER_VALIDATE_EMAIL) ||
            !filter_var($email->receiver->email, FILTER_VALIDATE_EMAIL)
        ) {
            error_log(
                "Foute email: '" .
                    $email->sender->email .
                    "' of '" .
                    $email->receiver->email .
                    "'",
            );
            return false;
        }

        // We want to send mails from a specific account, to keep track of how many emails are sent, and to be able to use an alternative SMTP server in the future.
        // Server settings

        if (empty($_ENV["MAILSERVER_SMTP_HOST"])) {
            throw new Exception(
                "Can't send emails. Make sure variable mailserver_smtp_host is set in .env file! For Test and Dev: use a free Mailtrap account.",
            );
        } elseif (empty($_ENV["MAILSERVER_SMTP_USERNAME"])) {
            throw new Exception(
                "Can't send emails. Make sure variable mailserver_smtp_usesrname is set in .env file! For Test and Dev: use a free Mailtrap account.",
            );
        } elseif (empty($_ENV["MAILSERVER_SMTP_PASSWORD"])) {
            throw new Exception(
                "Can't send emails. Make sure variable mailserver_smtp_password is set in .env file! For Test and Dev: use a free Mailtrap account.",
            );
        }

        $PHPMailer = new PHPMailer(true); // Passing `true` enables exceptions

        $PHPMailer->isSMTP(); // Set mailer to use SMTP
        $PHPMailer->Host = $_ENV["MAILSERVER_SMTP_HOST"]; // Specify main and backup SMTP servers
        $PHPMailer->SMTPAuth = true; // Enable SMTP authentication
        $PHPMailer->Username = $_ENV["MAILSERVER_SMTP_USERNAME"]; // SMTP username
        $PHPMailer->Password = $_ENV["MAILSERVER_SMTP_PASSWORD"]; // SMTP password
        $PHPMailer->SMTPSecure = "tls"; // Enable TLS encryption, `ssl` also accepted
        $PHPMailer->Port = 587; // TCP port to connect to

        //Recipients
        $PHPMailer->setFrom($email->sender->email, $email->sender->naam);
        $PHPMailer->addAddress($email->receiver->email, $email->receiver->naam); // Add a recipient

        //Content
        $PHPMailer->isHTML(true); // Set email format to HTML
        $PHPMailer->Subject = $email->titel;
        $PHPMailer->msgHTML($email->body);

        // Optionally add unsubscribe header
        $PHPMailer->addCustomHeader(
            "List-Unsubscribe",
            "<mailto:unsubscribe@skcvolleybal.nl>",
            "<http://www.skcvolleybal.nl/unsubscribe>",
        );

        $this->PrintEmail($email);

        if (!$PHPMailer->send()) {
            error_log("Mailer Error: " . $PHPMailer->ErrorInfo);
            return false;
        }

        return true;
    }

    private function PrintEmail(Email $email): void
    {
        // Niet naar de HTTP-response schrijven: deze endpoints zijn niet
        // geauthenticeerd en zouden anders persoonsgegevens publiceren.
        error_log(
            "From: " .
                $email->sender->naam .
                " (" .
                $email->sender->email .
                ")",
        );
        error_log(
            "To: " .
                $email->receiver->naam .
                " (" .
                $email->receiver->email .
                ")",
        );
        error_log($email->titel);
        error_log($email->body);
    }
}
