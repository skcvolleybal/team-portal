<?php

class AanwezigheidGateway
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function GetAanwezigheden($userId)
    {
        $query = "SELECT *
                  FROM TeamPortal_wedstrijdaanwezigheden
                  WHERE user_id = :userId";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];

        return $this->database->Execute($query, $params);
    }

    public function GetAanwezigheid($userId, $matchId)
    {
        $query = "SELECT *
                  FROM TeamPortal_wedstrijdaanwezigheden
                  WHERE user_id = :userId and match_id = :matchId";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":matchId", $matchId, PDO::PARAM_STR),
        ];

        $result = $this->database->Execute($query, $params);
        if (count($result) > 0) {
            return $result[0];
        }
        return null;
    }

    public function UpdateAanwezigheid($userId, $matchId, $aanwezigheid)
    {
        if (!in_array($aanwezigheid, ['Ja', 'Nee', 'Misschien'])) {
            throw new Exception("Aanwezigheid $aanwezigheid bestaat niet");
        }

        $wedstrijdAanwezigheid = $this->GetAanwezigheid($userId, $matchId);
        if ($wedstrijdAanwezigheid) {
            $this->Update($userId, $matchId, $aanwezigheid);
        } else {
            $this->Insert($userId, $matchId, $aanwezigheid);
        }
    }

    private function Update($userId, $matchId, $aanwezigheid)
    {
        $query = "UPDATE teamportal_wedstrijdaanwezigheden
                  set aanwezigheid = :aanwezigheid
                  WHERE user_id = :userId and match_id = :matchId";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":matchId", $matchId, PDO::PARAM_STR),
            new Param(":aanwezigheid", $aanwezigheid, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    private function Insert($userId, $matchId, $aanwezigheid)
    {
        $query = "INSERT INTO teamportal_wedstrijdaanwezigheden(user_id, match_id, aanwezigheid)
                  VALUES (:userId, :matchId, :aanwezigheid)";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":matchId", $matchId, PDO::PARAM_STR),
            new Param(":aanwezigheid", $aanwezigheid, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }
}
