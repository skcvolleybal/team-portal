<?php

include_once 'Param.php';

class AanwezigheidGateway
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function GetWedstrijdAanwezigheden($userId)
    {
        $query = "SELECT *
                  FROM TeamPortal_wedstrijdaanwezigheid
                  WHERE user_id = :userId and date >= CURRENT_DATE()";
        $params = [new Param(":userId", 542, PDO::PARAM_INT)];

        return $this->database->Execute($query, $params);
    }

    public function GetAanwezigheid($userId, $matchId)
    {
        $query = "SELECT *
                  FROM TeamPortal_wedstrijdaanwezigheid
                  WHERE user_id = :userId and date >= CURRENT_DATE()";
        $params = [new Param(":userId", 542, PDO::PARAM_INT)];

        $result = $this->database->Execute($query, $params);
        if (count($result) > 0) {
            return $result[0];
        }
        return null;
    }

    public function UpdateWedstrijdAanwezigheden($userId, $matchId, $aanwezigheid)
    {
        if (!in_array($aanwezigheid, ['Ja', 'Nee', 'Misschien'])) {
            throw new Exception("Aanwezigheid $aanwezigheid bestaat niet");
        }

        $wedstrijdAanwezigheid = $this->GetAanwezigheid($userId, $matchId);
        if ($wedstrijdAanwezigheid) {
            $this->UpdateAanwezigheid($userId, $matchId, $aanwezigheid);
        } else {
            $this->InsertAanwezigheid($userId, $matchId, $aanwezigheid);
        }
    }

    private function UpdateAanwezigheid($userId, $matchId, $aanwezigheid)
    {
        $query = "UPDATE TeamPortal_wedstrijdaanwezigheid
                  set aanwezigheid = :aanwezigheid
                  WHERE user_id = :userId and match_id = :matchId";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":matchId", $matchId, PDO::PARAM_INT),
            new Param(":aanwezigheid", $aanwezigheid, PDO::PARAM_STR),
        ];

        $result = $this->database->Execute($query, $params);
    }

    private function InsertAanwezigheid($userId, $matchId, $aanwezigheid)
    {
        $query = "INSERT INTO TeamPortal_wedstrijdaanwezigheid(user_id, match_id, aanwezigheid)
                  VALUES (:userId, :matchId, :aanwezigheid)";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":matchId", $matchId, PDO::PARAM_INT),
            new Param(":aanwezigheid", $aanwezigheid, PDO::PARAM_STR),
        ];

        $result = $this->database->Execute($query, $params);
    }
}
