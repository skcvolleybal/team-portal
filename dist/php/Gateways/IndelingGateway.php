<?php

class IndelingGateway
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }
    public function GetFluitbeurten($userId)
    {
        $query = "SELECT W.*, G.title as telteam, U.name as scheidsrechter
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id
                  LEFT JOIN J3_users U on U.id = W.scheidsrechter_id
                  WHERE W.scheidsrechter_id = :userId";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];
        $result = $this->database->Execute($query, $params);
        foreach ($result as &$row) {
            $row['telteam'] = ConvertToNevoboName($row['telteam']);
        }
        return $result;
    }

    public function GetTelbeurten($userId)
    {
        $query = "SELECT W.*, G.title as telteam, U.name as scheidsrechter
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id
                  INNER JOIN J3_user_usergroup_map M on W.telteam_id = M.group_id
                  LEFT JOIN J3_users U on U.id = W.scheidsrechter_id
                  WHERE M.user_id = :userId";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];
        $result = $this->database->Execute($query, $params);
        foreach ($result as &$row) {
            $row['telteam'] = ConvertToNevoboName($row['telteam']);
        }
        return $result;
    }

    public function GetZaalwachtForUserId($userId)
    {
        $query = "SELECT Z.*, title as team
                  FROM TeamPortal_zaalwacht Z
                  INNER JOIN J3_user_usergroup_map M on Z.team_id = M.group_id
                  INNER JOIN J3_usergroups G ON Z.team_id = G.id
                  WHERE M.user_id = :userId and Z.date >= CURRENT_DATE()";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];
        return $this->database->Execute($query, $params);
    }

    public function GetIndeling()
    {
        $query = "SELECT
                    W.match_id,
                    U.name as scheidsrechter,
                    G.title as telteam
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_users U on W.scheidsrechter_id = U.id
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id";
        return $this->database->Execute($query);
    }

    public function GetScheidsrechters()
    {
        $query = "SELECT
                    U.name AS naam,
                    C.cb_scheidsrechterscode AS niveau,
                    COUNT(W.user_id) AS gefloten,
                    team
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  LEFT JOIN (
                    SELECT user_id, group_id, title as team
                    FROM J3_user_usergroup_map M
                    INNER JOIN J3_usergroups G ON M.group_id = G.id
                    WHERE G.parent_id = (SELECT id FROM J3_usergroups WHERE title = 'Teams')) G2 ON U.id = G2.user_id
                  LEFT JOIN j3_comprofiler C ON C.user_id = U.id
                  LEFT JOIN scheidsapp_matches W ON W.user_id = U.id
                  WHERE G.id IN (SELECT id FROM J3_usergroups WHERE title = 'Scheidsrechters')
                  GROUP BY U.name";
        return $this->database->Execute($query);
    }

    public function GetZaalwachtTeams()
    {
        $query = "SELECT G.title as team, count(W.telteam_id) as geteld, count(Z.id) as zaalwacht
                  FROM J3_usergroups G
                  LEFT JOIN TeamPortal_wedstrijden W ON W.telteam_id = G.id
                  LEFT JOIN TeamPortal_zaalwacht Z ON Z.team_id = G.id
                  WHERE G.id in (
                    SELECT id FROM J3_usergroups WHERE parent_id = (
                      SELECT id FROM J3_usergroups WHERE title = 'Teams'
                    )
                  )
                  GROUP BY G.title
                  ORDER BY SUBSTRING(team, 1, 1), LENGTH(team), team";
        return $this->database->Execute($query);
    }

    public function GetZaalwachtIndeling()
    {
        $query = "SELECT Z.date, G.title as team
                  FROM TeamPortal_zaalwacht Z
                  INNER JOIN J3_usergroups G ON Z.team_id = G.id";
        return $this->database->Execute($query);
    }

    public function UpdateScheidscoZaalwacht($datum, $team)
    {
        $zaalwacht = $this->GetZaalwacht($datum);
        $teamId = $this->GetTeamId($team);

        if ($zaalwacht != null) {
            if (empty($team)) {
                $this->DeleteZaalwacht($zaalwacht['id']);
            } else {
                $this->UpdateZaalwacht($zaalwacht['id'], $teamId);
            }
        } else {
            $this->InsertZaalwacht($datum, $teamId);
        }
    }

    public function GetZaalwacht($date)
    {
        $query = "SELECT * FROM TeamPortal_zaalwacht WHERE date = :date";
        $params = [new Param(":date", $date, PDO::PARAM_STR)];
        $zaalwachten = $this->database->Execute($query, $params);
        if (count($zaalwachten) == 0) {
            return null;
        }
        return $zaalwachten[0];
    }

    private function GetTeamId($naam)
    {
        if (empty($naam)) {
            return null;
        }
        if ($naam[0] == "D") {
            $team = "Dames " . substr($naam, 1);
        } else {
            $team = "Heren " . substr($naam, 1);
        }

        $query = "SELECT * FROM J3_usergroups
                  WHERE title = :team";
        $params = [new Param(":team", $team, PDO::PARAM_STR)];
        $teams = $this->database->Execute($query, $params);
        if (count($teams) == 0) {
            InternalServerError("Unknown team: $team");
        }
        return $teams[0]['id'];
    }

    private function UpdateZaalwacht($id, $teamId)
    {
        $query = "UPDATE TeamPortal_zaalwacht
                  SET team_id = :teamId
                  WHERE id = :id";
        $params = [
            new Param(":id", $id, PDO::PARAM_INT),
            new Param(":teamId", $teamId, PDO::PARAM_INT),
        ];
        $this->database->Execute($query, $params);
    }

    private function InsertZaalwacht($date, $teamId)
    {
        $query = "INSERT INTO TeamPortal_zaalwacht (date, team_id)
                  VALUES (:date, :teamId)";
        $params = [
            new Param(":date", $date, PDO::PARAM_STR),
            new Param(":teamId", $teamId, PDO::PARAM_INT),
        ];
        $this->database->Execute($query, $params);
    }

    private function DeleteZaalwacht($id)
    {
        $query = "DELETE FROM TeamPortal_zaalwacht WHERE id = :id";
        $params = [
            new Param(":id", $id, PDO::PARAM_INT),
        ];
        $this->database->Execute($query, $params);
    }

    public function UpdateScheidscoWedstrijd($matchId, $scheidsrechter, $telteam)
    {
        $scheidsrechterId = $this->GetScheidsrechterId($scheidsrechter);
        $teamId = $this->GetTeamId($telteam);

        $wedstrijd = $this->GetWedstrijd($matchId);
        if ($wedstrijd != null) {
            $matchId = $wedstrijd['match_id'];
            if (empty($scheidsrechter) && empty($telteam)) {
                $this->DeleteWedstrijd($matchId);
            } else {
                $this->UpdateWedstrijd($matchId, $scheidsrechterId, $teamId);
            }
        } else {
            if (!empty($scheidsrechter) || !empty($telteam)) {
                $this->InsertWedstrijd($matchId, $scheidsrechterId, $teamId);
            }
        }
    }

    private function GetWedstrijd($matchId)
    {
        $query = "SELECT * FROM TeamPortal_wedstrijden WHERE match_id = :matchId";
        $params = [
            new Param(":matchId", $matchId, PDO::PARAM_STR),
        ];
        $wedstrijden = $this->database->Execute($query, $params);
        if (count($wedstrijden) == 0) {
            return null;
        };
        return $wedstrijden[0];
    }

    private function GetScheidsrechterId($scheidsrechter)
    {
        if (empty($scheidsrechter)) {
            return null;
        }

        $query = "SELECT U.id, name
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE U.name = :scheidsrechter and
                        G.id in (SELECT id FROM J3_usergroups WHERE title = 'Scheidsrechters')";
        $params = [
            new Param(":scheidsrechter", $scheidsrechter, PDO::PARAM_STR),
        ];
        $scheidsrechters = $this->database->Execute($query, $params);
        if (count($scheidsrechters) == 0) {
            InternalServerError("Unknwon scheidsrechter: $scheidsrechter");
        };
        return $scheidsrechters[0]['id'];
    }

    private function InsertWedstrijd($matchId, $scheidsrechterId, $telTeamId)
    {
        $query = "INSERT INTO TeamPortal_wedstrijden (match_id, scheidsrechter_id, telteam_id)
                  VALUES (:matchId, :scheidsrechterId, :telTeamId)";
        $params = [
            new Param(":matchId", $matchId, PDO::PARAM_STR),
            new Param(":scheidsrechterId", $scheidsrechterId, PDO::PARAM_INT),
            new Param(":telTeamId", $telTeamId, PDO::PARAM_INT),
        ];
        $this->database->Execute($query, $params);
    }

    private function UpdateWedstrijd($matchId, $scheidsrechterId, $telTeamId)
    {
        $query = "UPDATE TeamPortal_wedstrijden
                  SET scheidsrechter_id = :scheidsrechterId, telteam_id = :telTeamId
                  WHERE match_id = :matchId";
        $params = [
            new Param(":matchId", $matchId, PDO::PARAM_STR),
            new Param(":scheidsrechterId", $scheidsrechterId, PDO::PARAM_INT),
            new Param(":telTeamId", $telTeamId, PDO::PARAM_INT),
        ];
        $this->database->Execute($query, $params);
    }

    private function DeleteWedstrijd($matchId)
    {
        $query = "DELETE FROM TeamPortal_wedstrijden WHERE match_id = :matchId";
        $params = [
            new Param(":matchId", $matchId, PDO::PARAM_STR),
        ];
        $this->database->Execute($query, $params);
    }
}
