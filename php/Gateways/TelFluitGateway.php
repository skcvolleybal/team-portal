<?php

class TelFluitGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetAllFluitEnTelbeurten(): iterable
    {
        $query = 'SELECT 
                    W.match_id as matchId,
                    U.name as scheidsrechter,
                    G.title as tellers
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_users U ON W.scheidsrechter_id = U.id
                  LEFT JOIN J3_usergroups G ON W.telteam_id = G.id';
        return $this->database->Execute($query);
    }

    public function GetFluitEnTelbeurten($userId): iterable
    {
        $query = 'SELECT
                    W.id,
                    W.match_id AS matchId,
                    W.scheidsrechter_id AS scheidsrechterId,
                    G.id as telteamId,
                    G.team AS telteam,
                    U.id as scheidsrechterId,
                    U.name AS scheidsrechter FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_users U ON W.scheidsrechter_id = U.id
                  LEFT JOIN (
                    SELECT M.user_id, G.id AS team_id, G.title AS team FROM J3_user_usergroup_map M
                    INNER JOIN J3_usergroups G ON M.group_id = G.id
                    WHERE id IN (
                        SELECT id FROM J3_usergroups WHERE parent_id IN (
                            SELECT id FROM J3_usergroups WHERE title = "Teams"
                        )
                    ) AND user_id = ?
                  ) G ON G.team_id = W.telteam_id
                  WHERE W.scheidsrechter_id = ? OR user_id = ?';
        $params = [$userId];
        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $newWedstrijd = new Wedstrijd($row->matchId, $row->id);
            $newWedstrijd->telteam = $row->telteamId ? new Team($row->telteam, $row->telteamId) : null;
            $newWedstrijd->scheidsrechter = $row->scheidsrechterId ? new Persoon($row->scheidsrechterId, $row->scheidsrechter) : null;

            $result[] = $newWedstrijd;
        }
        return $result;
    }

    public function GetFluitbeurten($userId): iterable
    {
        $query = 'SELECT
                    W.id,
                    W.match_id as matchId,
                    W.scheidsrechter_id as scheidsrechterId,
                    G.id as telteamId,
                    G.title as telteam,
                    U.id as scheidsrechterId,
                    U.name as scheidsrechter
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id
                  LEFT JOIN J3_users U on U.id = W.scheidsrechter_id
                  WHERE W.scheidsrechter_id = ?';
        $params = [$userId];
        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $newWedstrijd = new Wedstrijd($row->matchId, $row->id);
            $newWedstrijd->telteam = $row->telteamId ? new Team($row->telteam, $row->telteamId) : null;

            $result[] = $newWedstrijd;
        }
        return $result;
    }

    public function GetTelbeurten($userId): iterable
    {
        $query = 'SELECT
                    W.id,
                    W.match_id as matchId,
                    W.scheidsrechter_id as scheidsrechterId,
                    G.id as teamId,
                    G.title as telteam,
                    U.id as scheidsrechterId,
                    U.name as scheidsrechter
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id
                  INNER JOIN J3_user_usergroup_map M on W.telteam_id = M.group_id
                  LEFT JOIN J3_users U on U.id = W.scheidsrechter_id
                  WHERE M.user_id = ?';
        $params = [$userId];
        $result = $this->database->Execute($query, $params);
        $response = [];
        foreach ($result as &$row) {
            $newWedstrijd = new Wedstrijd($row->matchId, $row->id);
            $newWedstrijd->telteam = $row->teamId ? new Team($row->telteam, $row->teamId) : null;
            $newWedstrijd->scheidsrechter = $row->scheidsrechterId ? new Persoon($row->scheidsrechterId, $row->scheidsrechter) : null;

            $response[] = $newWedstrijd;
        }
        return $response;
    }

    public function GetIndeling(): iterable
    {
        $query = 'SELECT
                    W.match_id as matchId,
                    U.name as scheidsrechter,
                    G.title as tellers
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_users U on W.scheidsrechter_id = U.id
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id';
        return $this->database->Execute($query);
    }

    public function GetScheidsrechters(): iterable
    {
        $query = 'SELECT
                    U.id,
                    U.name AS naam,
                    C.cb_scheidsrechterscode AS niveau,
                    COUNT(W.scheidsrechter_id) AS gefloten,
                    teamId,
                    teamnaam
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  LEFT JOIN (
                    SELECT 
                      user_id, 
                      group_id as teamId, 
                      title as teamnaam
                    FROM J3_user_usergroup_map M
                    INNER JOIN J3_usergroups G ON M.group_id = G.id
                    WHERE G.parent_id = (SELECT id FROM J3_usergroups WHERE title = \'Teams\')) G2 ON U.id = G2.user_id
                  LEFT JOIN J3_comprofiler C ON C.user_id = U.id
                  LEFT JOIN TeamPortal_wedstrijden W ON W.scheidsrechter_id = U.id
                  WHERE G.id IN (SELECT id FROM J3_usergroups WHERE title = "Scheidsrechters")
                  GROUP BY U.name
                  ORDER BY gefloten, naam';
        $rows = $this->database->Execute($query);
        $result = [];
        foreach ($rows as $row) {
            $scheidsrechter = new Scheidsrechter($row->id, $row->naam, $row->niveau, $row->gefloten);
            $scheidsrechter->team = $row->teamId != null ? new Team($row->teamnaam, $row->teamId) : null;
            $result[] = $scheidsrechter;
        }
        return $result;
    }

    public function GetScheidsrechtersForWedstrijdenWithMatchId($matchIds): array
    {
        if (empty($matchIds)) {
            return [];
        }

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
        foreach ($matchIds as $matchId) {
            $params[] = $matchId;
        }
        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Scheidsrechter(
                $row->userId,
                $row->naam
            );
        }
        return $result;
    }

    private function GetSaveMatchQuery(array $matchIds): string
    {
        $matchList = '';
        $counter = 0;
        foreach ($matchIds as $matchId) {
            $matchList .= ' UNION SELECT :matchId' . $counter++ . ' as id';
            $ids[] = $matchId;
        }
        return addslashes(substr($matchList, 7));
    }

    public function GetWedstrijd(string $matchId): ?Wedstrijd
    {
        $query = 'SELECT
                    W.id,
                    match_id as matchId,
                    U.id as userId,
                    U.name as naam,
                    G.id as teamId,
                    G.title as teamnaam
                   FROM TeamPortal_wedstrijden W
                   LEFT JOIN J3_users U ON W.scheidsrechter_id = U.id
                   LEFT JOIN J3_usergroups G ON W.telteam_id = G.id
                   WHERE W.match_id = ?';
        $params = [$matchId];
        $rows = $this->database->Execute($query, $params);
        if (count($rows) != 1) {
            return null;
        }

        $row = $rows[0];
        $wedstrijd = new Wedstrijd($row->matchId, $row->id);
        if ($row->userId) {
            $wedstrijd->scheidsrechter = new Scheidsrechter($row->userId, $row->naam);
        }
        if ($row->teamId) {
            $wedstrijd->telteam = new Team($row->teamnaam, $row->teamId);
        }

        return $wedstrijd;
    }

    public function GetTelTeams(): array
    {
        $query = 'SELECT
                    G.id as telteamId,
                    G.title as teamnaam,
                    count(W.telteam_id) as geteld
                  FROM J3_usergroups G
                  LEFT JOIN TeamPortal_wedstrijden W ON W.telteam_id = G.id
                  WHERE G.id in (
                    SELECT id FROM J3_usergroups WHERE parent_id = (
                      SELECT id FROM J3_usergroups WHERE title = "Teams"
                    )
                  )
                  GROUP BY G.title
                  ORDER BY geteld, SUBSTRING(teamnaam, 1, 1), LENGTH(teamnaam), teamnaam';
        $rows = $this->database->Execute($query);
        $result = [];
        foreach ($rows as $row) {
            $team  = new Team($row->teamnaam, $row->telteamId);
            $team->aantalKeerGeteld = $row->geteld;
            $result[] = $team;
        }
        return $result;
    }

    public function Insert(Wedstrijd $wedstrijd)
    {
        $query = 'INSERT INTO TeamPortal_wedstrijden (match_id, scheidsrechter_id, telteam_id)
                  VALUES (?, ?, ?)';
        $params = [$wedstrijd->matchId, $wedstrijd->scheidsrechter->id ?? null, $wedstrijd->telteam->id ?? null];
        $this->database->Execute($query, $params);
    }

    public function Update(Wedstrijd $wedstrijd)
    {
        $query = 'UPDATE TeamPortal_wedstrijden
                  SET scheidsrechter_id = ?, telteam_id = ?
                  WHERE match_id = ?';
        $params = [$wedstrijd->scheidsrechter->id ?? null, $wedstrijd->telteam->id ?? null, $wedstrijd->matchId];
        $this->database->Execute($query, $params);
    }

    public function Delete(Wedstrijd $wedstrijd)
    {
        $query = 'DELETE FROM TeamPortal_wedstrijden WHERE match_id = ?';
        $params = [$wedstrijd->matchId];
        $this->database->Execute($query, $params);
    }
}
