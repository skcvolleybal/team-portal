<?php

class TelFluitGateway
{
    private $database;

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function GetFluitEnTelbeurten($userId)
    {
        $query = "SELECT
                    W.match_id AS matchId,
                    W.scheidsrechter_id AS scheidsrechterId,
                    G.team AS tellers,
                    U.name AS scheidsrechter FROM teamportal_wedstrijden W
                  LEFT JOIN J3_users U ON W.scheidsrechter_id = U.id
                  LEFT JOIN (
                    SELECT M.user_id, G.id AS team_id, G.title AS team FROM J3_user_usergroup_map M
                    INNER JOIN J3_usergroups G ON M.group_id = G.id
                    WHERE id IN (
                        SELECT id FROM J3_usergroups WHERE parent_id IN (
                            SELECT id FROM J3_usergroups WHERE title = 'Teams'
                        )
                    ) AND user_id = :userId
                  ) G ON G.team_id = W.telteam_id
                  WHERE W.scheidsrechter_id = :userId OR user_id = :userId";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];
        $result = $this->database->Execute($query, $params);
        foreach ($result as &$row) {
            $row['tellers'] = ToNevoboName($row['tellers']);
        }
        return $result;
    }

    public function GetFluitbeurten($userId)
    {
        $query = "SELECT
                    W.match_id as id,
                    W.scheidsrechter_id as scheidsrechterId,
                    G.title as tellers,
                    U.name as scheidsrechter
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id
                  LEFT JOIN J3_users U on U.id = W.scheidsrechter_id
                  WHERE W.scheidsrechter_id = :userId";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];
        $result = $this->database->Execute($query, $params);
        foreach ($result as &$row) {
            $row['tellers'] = ToNevoboName($row['tellers']);
        }
        return $result;
    }

    public function GetTelbeurten($userId)
    {
        $query = "SELECT
                    W.match_id as id,
                    W.scheidsrechter_id as scheidsrechterId,
                    G.title as tellers,
                    U.name as scheidsrechter
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id
                  INNER JOIN J3_user_usergroup_map M on W.telteam_id = M.group_id
                  LEFT JOIN J3_users U on U.id = W.scheidsrechter_id
                  WHERE M.user_id = :userId";
        $params = [new Param(":userId", $userId, PDO::PARAM_INT)];
        $result = $this->database->Execute($query, $params);
        foreach ($result as &$row) {
            $row['tellers'] = ToNevoboName($row['tellers']);
        }
        return $result;
    }

    public function GetIndeling()
    {
        $query = "SELECT
                    W.match_id as matchId,
                    U.name as scheidsrechter,
                    G.title as tellers
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_users U on W.scheidsrechter_id = U.id
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id";
        return $this->database->Execute($query);
    }

    public function GetScheidsrechters()
    {
        $query = "SELECT
                    U.id,
                    U.name AS naam,
                    C.cb_scheidsrechterscode AS niveau,
                    COUNT(W.scheidsrechter_id) AS gefloten,
                    team
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  LEFT JOIN (
                    SELECT user_id, group_id, title as team
                    FROM J3_user_usergroup_map M
                    INNER JOIN J3_usergroups G ON M.group_id = G.id
                    WHERE G.parent_id = (SELECT id FROM J3_usergroups WHERE title = 'Teams')) G2 ON U.id = G2.user_id
                  LEFT JOIN J3_comprofiler C ON C.user_id = U.id
                  LEFT JOIN TeamPortal_wedstrijden W ON W.scheidsrechter_id = U.id
                  WHERE G.id IN (SELECT id FROM J3_usergroups WHERE title = 'Scheidsrechters')
                  GROUP BY U.name
                  ORDER BY gefloten, naam";
        return $this->database->Execute($query);
    }

    public function GetTellersForWedstrijdenWithMatchId($matchIds)
    {
        $matchQuery = $this->GetSaveMatchQuery($matchIds);

        $query = "SELECT
                    W.match_id as matchId,
                    U.id as userId,
                    U.name as naam,
                    U.email,
                    G.title as tellers
                  FROM TeamPortal_wedstrijden W
                  INNER JOIN ($matchQuery) matchIds ON matchIds.id = W.match_id
                  INNER JOIN J3_user_usergroup_map M ON M.group_id = W.telteam_id
                  INNER JOIN J3_usergroups G ON G.id = M.group_id
                  INNER JOIN J3_users U ON M.user_id = U.id";
        $params = [];
        $counter = 0;
        foreach ($matchIds as $matchId) {
            $params[] = new Param(":matchId" . $counter++, $matchId, PDO::PARAM_STR);
        }
        return $this->database->Execute($query, $params);
    }

    public function GetScheidsrechtersForWedstrijdenWithMatchId($matchIds)
    {
        $matchQuery = $this->GetSaveMatchQuery($matchIds);

        $query = "SELECT
                    W.match_id as matchId,
                    U.id as userId,
                    U.name as naam,
                    U.email,
                    G.title as team
                  FROM TeamPortal_wedstrijden W
                  INNER JOIN ($matchQuery) matchIds ON matchIds.id = W.match_id
                  INNER JOIN J3_users U ON W.scheidsrechter_id = U.id
                  LEFT JOIN (
                      SELECT user_id, group_id, title
                      FROM J3_user_usergroup_map M
                      INNER JOIN J3_usergroups G ON G.id = M.group_id
                      WHERE G.id in (
                        SELECT id FROM J3_usergroups WHERE parent_id = (
                          SELECT id FROM J3_usergroups WHERE title = 'Teams'
                        )
                      )
                  ) as G ON G.user_id = U.id";
        $params = [];
        $counter = 0;
        foreach ($matchIds as $matchId) {
            $params[] = new Param(":matchId" . $counter++, $matchId, PDO::PARAM_STR);
        }
        return $this->database->Execute($query, $params);
    }

    private function GetSaveMatchQuery($matchIds)
    {
        $matchList = "";
        $counter = 0;
        foreach ($matchIds as $matchId) {
            $matchList .= " UNION SELECT :matchId" . $counter++ . " as id";
            $ids[] = $matchId;
        }
        return addslashes(substr($matchList, 7));
    }

    public function GetWedstrijd($matchId)
    {
        $query = "SELECT
                    match_id as matchId,
                    scheidsrechter_id as scheidsrechterId,
                    telteam_id as telteamId
                   FROM TeamPortal_wedstrijden WHERE match_id = :matchId";
        $params = [
            new Param(":matchId", $matchId, PDO::PARAM_STR),
        ];
        $wedstrijden = $this->database->Execute($query, $params);
        if (count($wedstrijden) == 0) {
            return null;
        };
        return $wedstrijden[0];
    }

    public function GetTelTeams()
    {
        $query = "SELECT
                    G.title as naam,
                    count(W.telteam_id) as geteld
                  FROM J3_usergroups G
                  LEFT JOIN TeamPortal_wedstrijden W ON W.telteam_id = G.id
                  WHERE G.id in (
                    SELECT id FROM J3_usergroups WHERE parent_id = (
                      SELECT id FROM J3_usergroups WHERE title = 'Teams'
                    )
                  )
                  GROUP BY G.title
                  ORDER BY geteld, SUBSTRING(naam, 1, 1), LENGTH(naam), naam";
        return $this->database->Execute($query);
    }

    public function Insert($matchId, $scheidsrechterId, $telTeamId)
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

    public function Update($matchId, $scheidsrechterId, $telTeamId)
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

    public function Delete($matchId)
    {
        $query = "DELETE FROM TeamPortal_wedstrijden WHERE match_id = :matchId";
        $params = [
            new Param(":matchId", $matchId, PDO::PARAM_STR),
        ];
        $this->database->Execute($query, $params);
    }
}
