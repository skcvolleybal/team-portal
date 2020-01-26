<?php

namespace TeamPortal\Gateways;

use TeamPortal\Common\Database;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities;

class TelFluitGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetAllFluitEnTelbeurten(): array
    {
        $query = 'SELECT 
                    W.id,
                    W.match_id AS matchId,
                    W.timestamp,
                    W.is_veranderd as isVeranderd,
                    G.id AS telteamId,
                    G.title AS telteam,
                    U.id AS userId,
                    U.name AS naam,
                    U.email
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_users U ON W.scheidsrechter_id = U.id
                  LEFT JOIN J3_usergroups G ON W.telteam_id = G.id';
        $rows = $this->database->Execute($query);
        return $this->MapToWedstrijden($rows);
    }

    public function GetFluitEnTelbeurten(Entities\Persoon $user): array
    {
        $query = 'SELECT
                    W.id,
                    W.match_id AS matchId,
                    W.timestamp,
                    W.is_veranderd as isVeranderd,
                    G.id AS telteamId,
                    G.title AS telteam,
                    U.id AS userId,
                    U.name AS naam,
                    U.email
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id
                  LEFT JOIN J3_users U on U.id = W.scheidsrechter_id
                  WHERE (W.scheidsrechter_id = ? OR W.telteam_id = ?) AND
                        W.timestamp >= CURRENT_TIMESTAMP()';
        $params = [
            $user->id,
            $user->team ? $user->team->id : -1
        ];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToWedstrijden($rows);
    }

    public function GetFluitbeurten(Entities\Persoon $user): array
    {
        $query = 'SELECT
                    W.id,
                    W.match_id AS matchId,
                    W.timestamp,
                    W.is_veranderd as isVeranderd,
                    G.id AS telteamId,
                    G.title AS telteam,
                    U.id AS userId,
                    U.name AS naam,
                    U.email
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id
                  LEFT JOIN J3_users U on U.id = W.scheidsrechter_id
                  WHERE W.scheidsrechter_id = ? AND
                        W.timestamp >= CURRENT_TIMESTAMP()';
        $params = [$user->id];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToWedstrijden($rows);
    }

    public function GetTelbeurten(Entities\Persoon $user): array
    {
        $query = 'SELECT
                    W.id,
                    W.match_id AS matchId,
                    W.timestamp,
                    W.is_veranderd as isVeranderd,
                    G.id AS telteamId,
                    G.title AS telteam,
                    U.id AS userId,
                    U.name AS naam,
                    U.email
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_usergroups G on W.telteam_id = G.id
                  INNER JOIN J3_user_usergroup_map M on W.telteam_id = M.group_id
                  LEFT JOIN J3_users U on U.id = W.scheidsrechter_id
                  WHERE M.user_id = ? AND
                        W.timestamp >= CURRENT_TIMESTAMP()';
        $params = [$user->id];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToWedstrijden($rows);
    }

    public function GetScheidsrechters(): array
    {
        $query = 'SELECT
                    U.id,
                    U.name AS naam,
                    U.email,
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
                      group_id AS teamId, 
                      title AS teamnaam
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
            $scheidsrechter = new Entities\Scheidsrechter(
                new Entities\Persoon($row->id, $row->naam, $row->email),
                $row->niveau,
                $row->gefloten
            );
            $scheidsrechter->team = $row->teamId != null ? new Entities\Team($row->teamnaam, $row->teamId) : null;
            $result[] = $scheidsrechter;
        }
        return $result;
    }

    public function GetWedstrijd(string $matchId): ?Entities\Wedstrijd
    {
        $query = 'SELECT
                    W.id,
                    W.match_id AS matchId,
                    W.timestamp,
                    W.is_veranderd as isVeranderd,
                    G.id AS telteamId,
                    G.title AS telteam,
                    U.id AS userId,
                    U.name AS naam,
                    U.email
                   FROM TeamPortal_wedstrijden W
                   LEFT JOIN J3_users U ON W.scheidsrechter_id = U.id
                   LEFT JOIN J3_usergroups G ON W.telteam_id = G.id
                   WHERE W.match_id = ?';
        $params = [$matchId];
        $rows = $this->database->Execute($query, $params);
        return count($rows) > 0 ? $this->MapToWedstrijden($rows)[0] : new Entities\Wedstrijd($matchId);
    }

    public function GetTelTeams(): array
    {
        $query = 'SELECT
                    G.id AS telteamId,
                    G.title AS teamnaam,
                    count(W.telteam_id) AS geteld
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
            $team  = new Entities\Team($row->teamnaam, $row->telteamId);
            $team->aantalKeerGeteld = $row->geteld;
            $result[] = $team;
        }
        return $result;
    }

    public function Insert(Entities\Wedstrijd $wedstrijd): void
    {
        $query = 'INSERT INTO TeamPortal_wedstrijden (match_id, scheidsrechter_id, telteam_id)
                  VALUES (?, ?, ?)';
        $params = [
            $wedstrijd->matchId,
            $wedstrijd->scheidsrechter !== null ? $wedstrijd->scheidsrechter->id : null,
            $wedstrijd->telteam !== null ? $wedstrijd->telteam->id : null
        ];
        $this->database->Execute($query, $params);
    }

    public function Update(Entities\Wedstrijd $wedstrijd): void
    {
        $query = 'UPDATE TeamPortal_wedstrijden
                  SET scheidsrechter_id = ?, telteam_id = ?, is_veranderd = ?, timestamp = ?
                  WHERE match_id = ?';
        $params = [
            $wedstrijd->scheidsrechter !== null ? $wedstrijd->scheidsrechter->id : null,
            $wedstrijd->telteam !== null ? $wedstrijd->telteam->id : null,
            $wedstrijd->isVeranderd,
            DateFunctions::GetMySqlTimestamp($wedstrijd->timestamp),
            $wedstrijd->matchId
        ];
        $this->database->Execute($query, $params);
    }

    public function Delete(Entities\Wedstrijd $wedstrijd): void
    {
        $query = 'DELETE FROM TeamPortal_wedstrijden WHERE match_id = ?';
        $params = [$wedstrijd->matchId];
        $this->database->Execute($query, $params);
    }

    private function MapToWedstrijden(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $newWedstrijd = new Entities\Wedstrijd($row->matchId, $row->id);
            $newWedstrijd->timestamp = $row->timestamp !== null ? DateFunctions::CreateDateTime(substr($row->timestamp, 0, 10), substr($row->timestamp, 11, 8)) : null;
            $newWedstrijd->isVeranderd = $row->isVeranderd;
            $newWedstrijd->telteam = $row->telteamId ? new Entities\Team($row->telteam, $row->telteamId) : null;
            $newWedstrijd->scheidsrechter = $row->userId ? new Entities\Persoon($row->userId, $row->naam, $row->email) : null;

            $result[] = $newWedstrijd;
        }
        return $result;
    }
}
