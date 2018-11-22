<?php

include_once 'NevoboGateway.php';

class BarcieGateway
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
        $this->nevoboGateway = new NevoboGateway();
    }

    public function GetDateId($date)
    {
        $query = "SELECT id
                  FROM barcie_days
                  WHERE date = :date";
        $params = [
            new Param(":date", $date, PDO::PARAM_STR),
        ];

        $result = $this->database->Execute($query, $params);
        if (count($result) == 0) {
            return null;
        }
        return $result[0]['id'];
    }

    public function GetBarcieDagen()
    {
        $query = "SELECT *
                  FROM barcie_days
                  WHERE CURRENT_DATE() <= date";
        return $this->database->Execute($query);
    }

    public function GetBeschikbaarheden($userId)
    {
        $query = "SELECT D.date, A.beschikbaarheid
                  FROM barcie_availability A
                  INNER JOIN barcie_days D on A.day_id = D.id
                  WHERE A.user_id = :userId and D.date >= CURRENT_DATE()";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
        ];

        return $this->database->Execute($query, $params);
    }

    public function GetBeschikbaarheid($userId, $dayId)
    {
        $query = "SELECT *
                  FROM barcie_availability
                  WHERE user_id = :userId and day_id = :dayId";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":dayId", $dayId, PDO::PARAM_INT),
        ];

        $result = $this->database->Execute($query, $params);
        if (count($result) == 0) {
            return null;
        }
        return $result[0];
    }

    public function UpdateBeschikbaarheid($id, $beschikbaarheid)
    {
        $this->CheckBeschikbaarheid($beschikbaarheid);
        $query = "UPDATE barcie_availability
                  SET beschikbaarheid = :beschikbaarheid
                  WHERE id = :id";
        $params = [
            new Param(":id", $id, PDO::PARAM_INT),
            new Param(":beschikbaarheid", $beschikbaarheid, PDO::PARAM_STR),
        ];

        return $this->database->Execute($query, $params);
    }

    public function InsertBeschikbaarheid($userId, $dayId, $beschikbaarheid)
    {
        $this->CheckBeschikbaarheid($beschikbaarheid);
        $query = "INSERT INTO barcie_availability (day_id, user_id, availability)
                  VALUES (:dayId, :userId, :beschikbaarheid)";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":dayId", $dayId, PDO::PARAM_INT),
            new Param(":beschikbaarheid", $beschikbaarheid, PDO::PARAM_INT),
        ];

        return $this->database->Execute($query, $params);
    }

    private function CheckBeschikbaarheid($beschikbaarheid)
    {
        if (!in_array($beschikbaarheid, ["Ja", "Nee", "Onbekend"])) {
            InternalServerError("$beschikbaarheid is niet een van de opties");
        }
    }

    public function SetBHV($id, $isBHV)
    {
        $isBHV = $isBHV ? 1 : 0;
        $query = "UPDATE barcie_availability
                  SET is_bhv = :isBHV
                  WHERE id = :id";
        $params = [
            new Param(":shift", $shift, PDO::PARAM_INT),
            new Param(":isBHV", $isBHV, PDO::PARAM_INT),
        ];

        return $this->database->Execute($query, $params);
    }

    public function GetBarcieMapItem($dayId, $userId, $shift)
    {
        $query = "SELECT * FROM barcie_schedule_map
                  WHERE day_id = :dayId and
                        user_id = :userId and
                        shift = :shift";
        $params = [
            new Param(":dayId", $dayId, PDO::PARAM_INT),
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":shift", $shift, PDO::PARAM_INT),
        ];

        return $this->database->Execute($query, $params);
    }

    public function AddBarcie($dayId, $userId, $shift)
    {
        $query = "INSERT INTO barcie_schedule_map (day_id, user_id, shift)
                  VALUES (:dayId, :userId, :shift)";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":dayId", $dayId, PDO::PARAM_INT),
            new Param(":shift", $shift, PDO::PARAM_INT),
        ];

        return $this->database->Execute($query, $params);
    }

    public function DeleteBarcie($id)
    {
        $query = "DELETE FROM barcie_schedule_map
                  WHERE id = :id";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":dayId", $dayId, PDO::PARAM_INT),
            new Param(":shift", $shift, PDO::PARAM_INT),
        ];

        return $this->database->Execute($query, $params);
    }
}
