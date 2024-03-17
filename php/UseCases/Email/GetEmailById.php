<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\Database;


class GetEmailById implements Interactor
{

    function __construct(Database $database)
    {
        $this->database = $database;
    }


    public function Execute(object $data = null)
    {


        $params = [$data->id];
        $query = "SELECT * FROM TeamPortal_email where id = ?";
        $email = $this->database->Execute($query, $params);

        if (count($email) > 0) {
            return $email;
        } else {
            print("No e-mail found with this id");
        }

     }

    }