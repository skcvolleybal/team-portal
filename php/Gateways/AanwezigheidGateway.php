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

    public function GetAanwezigheidForTeam($matchIds)
    {
        $matchIdstring = implode(",", $matchIds);
        $query = "SELECT A.*, U.name as naam
                  FROM TeamPortal_wedstrijdaanwezigheden A
                  INNER JOIN J3_users U ON A.user_id = U.id
                  WHERE match_id IN (:matchIdstring)
                  ORDER BY match_id, U.name";
        $params = [
            new Param(":matchIdstring", $matchIdstring, PDO::PARAM_STR),
        ];

        return $this->database->Execute($query, $params);
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
        $query = "UPDATE TeamPortal_wedstrijdaanwezigheden
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
        $query = "INSERT INTO TeamPortal_wedstrijdaanwezigheden (user_id, match_id, aanwezigheid)
                  VALUES (:userId, :matchId, :aanwezigheid)";
        $params = [
            new Param(":userId", $userId, PDO::PARAM_INT),
            new Param(":matchId", $matchId, PDO::PARAM_STR),
            new Param(":aanwezigheid", $aanwezigheid, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    private function GetSkcTeam($team)
    {
        return ($team[5] == 'D' ? "Dames " : "Heren ") . substr($team, 7);
    }
}
