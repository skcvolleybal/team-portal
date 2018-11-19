<?php

class FluitBeschikbaarheidGateway
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function GetFluitBeschikbaarheid($userId)
    {
        $query = "SELECT *
                  FROM TeamPortal_fluitbeschikbaarheid
                  WHERE user_id = :userId";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];

        return $this->database->Execute($query, $params);
    }

    private function GetBeschikbaarheid($userId, $datum, $tijd)
    {
        $query = "SELECT *
                  FROM TeamPortal_fluitbeschikbaarheid
                  WHERE user_id = :userId and datum = :datum and tijd = :tijd";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":datum", $datum, PDO::PARAM_STR),
            new Param(":tijd", $tijd, PDO::PARAM_STR),
        ];

        $result = $this->database->Execute($query, $params);
        if (count($result) > 0) {
            return $result[0];
        }
        return null;
    }

    public function GetAllBeschikbaarheid($datum, $tijd)
    {
        $query = "SELECT *
                  FROM TeamPortal_fluitbeschikbaarheid
                  WHERE datum = :datum and tijd = :tijd";
        $params = [
            new Param(":datum", $datum, PDO::PARAM_STR),
            new Param(":tijd", $tijd, PDO::PARAM_STR),
        ];

        return $this->database->Execute($query, $params);
    }

    public function UpdateBeschikbaarheid($userId, $datum, $tijd, $beschikbaarheid)
    {
        if (!$this->VerifyDate($datum)) {
            InternalServerError("Unknown date: $datum");
        }
        if (!$this->VerifyTime($tijd)) {
            InternalServerError("Unknown time: $tijd");
        }
        if (!in_array($beschikbaarheid, ["Ja", "Nee", "Onbekend"])) {
            InternalServerError("Unknown beschikbaarheid: $beschikbaarheid");
        }

        $dbBeschikbaarheid = $this->GetBeschikbaarheid($userId, $datum, $tijd);
        if ($dbBeschikbaarheid == null) {
            $this->Insert($userId, $datum, $tijd, $beschikbaarheid);
        } else {
            $this->Update($dbBeschikbaarheid['id'], $beschikbaarheid);
        }
    }

    private function Insert($userId, $datum, $tijd, $beschikbaarheid)
    {
        $query = "INSERT TeamPortal_fluitbeschikbaarheid
                  SET user_id = :userId,
                      datum = :datum,
                      tijd = :tijd,
                      beschikbaarheid = :beschikbaarheid";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":datum", $datum, PDO::PARAM_STR),
            new Param(":tijd", $tijd, PDO::PARAM_STR),
            new Param(":beschikbaarheid", $beschikbaarheid, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    private function Update($id, $beschikbaarheid)
    {
        $query = "UPDATE TeamPortal_fluitbeschikbaarheid
                  SET beschikbaarheid = :beschikbaarheid
                  WHERE id = :id";

        $params = [
            new Param(":id", $id, PDO::PARAM_INT),
            new Param(":beschikbaarheid", $beschikbaarheid, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    private function VerifyDate($date)
    {
        return DateTime::createFromFormat('Y-m-d', $date);
    }

    private function VerifyTime($time)
    {
        return DateTime::createFromFormat('H:i:s', $time);
    }
}
