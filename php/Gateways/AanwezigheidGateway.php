<?php

include_once 'NevoboGateway.php';

class AanwezigheidGateway
{
    public function __construct($database)
    {
        $this->database = $database;
        $this->nevoboGateway = new NevoboGateway();
    }

    public function GetAanwezighedenForUser($userId)
    {
        $query = 'SELECT
                    id,
                    match_id as matchId,
                    user_id as userId,
                    aanwezigheid
                  FROM TeamPortal_aanwezigheden
                  WHERE user_id = :userId and is_coach != "Y"';
        $params = [new Param(Column::UserId, $userId, PDO::PARAM_INT)];

        return $this->database->Execute($query, $params);
    }

    public function GetAanwezigheid($userId, $matchId)
    {
        $query = 'SELECT *
                  FROM TeamPortal_aanwezigheden
                  WHERE user_id = :userId and match_id = :matchId and is_coach != "Y"';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
        ];

        $result = $this->database->Execute($query, $params);
        if (count($result) > 0) {
            return $result[0];
        }
        return null;
    }

    public function GetCoachAanwezigheid($userId, $matchId)
    {
        $query = 'SELECT *
                  FROM TeamPortal_aanwezigheden
                  WHERE user_id = :userId and match_id = :matchId and is_coach = "Y"';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
        ];

        $result = $this->database->Execute($query, $params);
        if (count($result) > 0) {
            return $result[0];
        }
        return null;
    }

    public function GetCoachAanwezigheden($userId)
    {
        $query = 'SELECT
                    id,
                    user_id as userId,
                    match_id as matchId,
                    aanwezigheid
                  FROM TeamPortal_aanwezigheden
                  WHERE user_id = :userId and is_coach = "Y"';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
        ];

        return $this->database->Execute($query, $params);
    }

    public function DeleteCoachAanwezigheid($userId, $matchId)
    {
        $query = 'DELETE FROM TeamPortal_aanwezigheden
                  WHERE user_id = :userId and match_id = :matchId and is_coach = "Y"';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    public function UpdateCoachAanwezigheid($userId, $matchId, $aanwezigheid)
    {
        $query = 'UPDATE TeamPortal_aanwezigheden
                  SET aanwezigheid = :aanwezigheid
                  WHERE user_id = :userId and match_id = :matchId and is_coach = "Y"';
        $params = [
            new Param(Column::IsAanwezig, $aanwezigheid, PDO::PARAM_STR),
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    public function InsertCoachAanwezigheid($userId, $matchId, $aanwezigheid)
    {
        $query = 'INSERT INTO TeamPortal_aanwezigheden
                  (match_id, user_id, aanwezigheid, is_coach)
                  VALUES (:matchId, :userId, :aanwezigheid, "Y")';
        $params = [
            new Param(Column::IsAanwezig, $aanwezigheid, PDO::PARAM_STR),
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    public function GetCoachAanwezighedenForTeam($team)
    {
        $query = 'SELECT
                    match_id as matchId,
                    U.name as naam,
                    A.aanwezigheid
                  FROM J3_usergroups G
                  INNER JOIN J3_user_usergroup_map M on G.id = M.group_id
                  INNER JOIN TeamPortal_aanwezigheden A ON A.user_id = M.user_id
                  INNER JOIN J3_users U on A.user_id = U.id
                  WHERE parent_id = (
                    SELECT id FROM J3_usergroups where title = :team
                  ) and A.is_coach = "Y"';
        $params = [
            new Param(':team', $team, PDO::PARAM_STR),
        ];
        return $this->database->Execute($query, $params);
    }

    public function GetAanwezighedenForTeam($team)
    {
        $team = ToSkcName($team);
        $query = 'SELECT A.*, U.* 
                  FROM TeamPortal_aanwezigheden A
                  INNER JOIN J3_users U ON A.user_id = U.id
                  INNER JOIN j3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN j3_usergroups G ON M.group_id = G.id
                  WHERE G.title = :team OR G.title = :coachteam
                  ORDER BY U.name;';
        $params = [
            new Param(':team', $team, PDO::PARAM_STR),
            new Param(':coachteam', 'Coach $team', PDO::PARAM_STR)];

        return $this->database->Execute($query, $params);
    }

    public function UpdateAanwezigheid($userId, $matchId, $aanwezigheid)
    {
        if (!in_array($aanwezigheid, ['Ja', 'Nee', 'Onbekend'])) {
            throw new InvalidArgumentException('Aanwezigheid $aanwezigheid bestaat niet');
        }

        $wedstrijdAanwezigheid = $this->GetAanwezigheid($userId, $matchId);
        if ($wedstrijdAanwezigheid) {
            if ($aanwezigheid == 'Onbekend') {
                $this->Delete($userId, $matchId);
            } else {
                $this->Update($userId, $matchId, $aanwezigheid);
            }
        } else {
            $this->Insert($userId, $matchId, $aanwezigheid);
        }
    }

    private function Update($userId, $matchId, $aanwezigheid)
    {
        $query = 'UPDATE TeamPortal_aanwezigheden
                  set aanwezigheid = :aanwezigheid
                  WHERE user_id = :userId and match_id = :matchId and is_coach != "Y"';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
            new Param(Column::IsAanwezig, $aanwezigheid, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    private function Insert($userId, $matchId, $aanwezigheid)
    {
        $query = 'INSERT INTO TeamPortal_aanwezigheden (user_id, match_id, is_aanwezig)
                  VALUES (:userId, :matchId, :isAanwezig)';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
            new Param(Column::IsAanwezig, $aanwezigheid, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    private function Delete($userId, $matchId)
    {
        $query = 'DELETE FROM TeamPortal_aanwezigheden
                  WHERE user_id = :userId and match_id = :matchId and is_coach != "Y"';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
        ];
        $this->database->Execute($query, $params);
    }
}
