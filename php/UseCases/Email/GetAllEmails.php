<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\Database;


class GetAllEmails implements Interactor
{

    function __construct(Database $database)
    {
        $this->database = $database;
    }


    public function Execute(object $data = null): array
    {
     
        $query = "SELECT id, sender_email, sender_naam, receiver_email, receiver_naam, titel, queue_date, send_date FROM TeamPortal_email ORDER BY id DESC";
        $emails = $this->database->Execute($query, []);

        if (count($emails) > 0) {
            return $emails;
        } else {
            return [];
        }

     }

    }