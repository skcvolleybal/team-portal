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
                  WHERE user_id = :userId';
        $params = [new Param(Column::UserId, $userId, PDO::PARAM_INT)];

        return $this->database->Execute($query, $params);
    }

    public function GetAanwezigheid($userId, $matchId)
    {
        $query = 'SELECT *
                  FROM TeamPortal_aanwezigheden
                  WHERE user_id = :userId and match_id = :matchId';
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

    public function UpdateCoachAanwezigheid($userId, $matchId, $isAanwezig)
    {
        $isAanwezig = $this->ToDatabaseBoolean($isAanwezig);
        $query = 'UPDATE TeamPortal_aanwezigheden
                  SET is_aanwezig = :isAanwezigheid
                  WHERE user_id = :userId and match_id = :matchId';
        $params = [
            new Param(Column::IsAanwezig, $isAanwezig, PDO::PARAM_STR),
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    public function GetAanwezighedenForTeams($team)
    {
        $team = ToSkcName($team);
        $query = 'SELECT 
                    A.*, 
                    U.name as naam,
                    G.title as rol
                  FROM TeamPortal_aanwezigheden A
                  INNER JOIN J3_users U ON A.user_id = U.id
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE G.title = :team OR G.title = :coachteam
                  ORDER BY U.name';
        $params = [
            new Param(':team', $team, PDO::PARAM_STR),
            new Param(':coachteam', "Coach $team", PDO::PARAM_STR)
        ];

        return $this->database->Execute($query, $params);
    }

    public function Update($userId, $matchId, $isAanwezig)
    {
        $isAanwezig = $this->ToDatabaseBoolean($isAanwezig);
        $query = 'UPDATE TeamPortal_aanwezigheden
                  set is_aanwezig = :isAanwezig
                  WHERE user_id = :userId and match_id = :matchId';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
            new Param(Column::IsAanwezig, $isAanwezig, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    public function Insert($userId, $matchId, $isAanwezig)
    {
        $isAanwezig = $this->ToDatabaseBoolean($isAanwezig);
        $query = 'INSERT INTO TeamPortal_aanwezigheden (user_id, match_id, is_aanwezig)
                  VALUES (:userId, :matchId, :isAanwezig)';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
            new Param(Column::IsAanwezig, $isAanwezig, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    public function Delete($userId, $matchId)
    {
        $query = 'DELETE FROM TeamPortal_aanwezigheden
                  WHERE user_id = :userId and match_id = :matchId';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::MatchId, $matchId, PDO::PARAM_STR),
        ];
        $this->database->Execute($query, $params);
    }

    private function ToDatabaseBoolean($value)
    {
        $value = strtolower($value);
        if (!in_array($value, ['ja', 'nee'])) {
            throw new InvalidArgumentException("Onbekende boolean variable '$value'");
        }

        if ($value == 'ja') {
            return 'Y';
        } else {
            return 'N';
        }
    }
}
