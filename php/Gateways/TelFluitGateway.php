<?php

namespace TeamPortal\Gateways;

use TeamPortal\Common\Database;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Scheidsrechter;
use TeamPortal\Entities\Team;
use TeamPortal\Entities\Wedstrijd;

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
                    U1.id AS scheidsrechterId,
                    U1.name AS scheidsrechter,
                    U1.email emailScheidsrechter,
                    U2.id AS idTeller1,
                    U2.name AS naamTeller1,
                    U2.email AS emailTeller1,
                    U3.id AS idTeller2,
                    U3.name AS naamTeller2,
                    U3.email emailTeller2
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_users U1 ON W.scheidsrechter_id = U1.id
                  LEFT JOIN J3_users U2 ON W.teller1_id = U2.id
                  LEFT JOIN J3_users U3 ON W.teller2_id = U3.id';
        $rows = $this->database->Execute($query);
        return $this->MapToWedstrijden($rows);
    }

    public function GetFluitEnTelbeurtenFor(Persoon $user): array
    {
        $query = 'SELECT
                    W.id,
                    W.match_id AS matchId,
                    W.timestamp,
                    W.is_veranderd as isVeranderd,
                    U1.id AS scheidsrechterId,
                    U1.name AS scheidsrechter,
                    U1.email emailScheidsrechter,
                    U2.id AS idTeller1,
                    U2.name AS naamTeller1,
                    U2.email AS emailTeller1,
                    U3.id AS idTeller2,
                    U3.name AS naamTeller2,
                    U3.email emailTeller2
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_users U1 on U1.id = W.scheidsrechter_id
                  LEFT JOIN J3_users U2 on U2.id = W.teller1_id
                  LEFT JOIN J3_users U3 on U3.id = W.teller2_id
                  WHERE (W.scheidsrechter_id = ? OR W.teller1_id = ? OR W.teller2_id = ?) AND
                        W.timestamp >= CURRENT_TIMESTAMP()';
        $params = [
            $user->id, $user->id, $user->id
        ];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToWedstrijden($rows);
    }

    public function GetFluitbeurten(Persoon $user): array
    {
        $query = 'SELECT
                    W.id,
                    W.match_id AS matchId,
                    W.timestamp,
                    W.is_veranderd as isVeranderd,
                    U1.id AS scheidsrechterId,
                    U1.name AS scheidsrechter,
                    U1.email emailScheidsrechter,
                    U2.id AS idTeller1,
                    U2.name AS naamTeller1,
                    U2.email AS emailTeller1,
                    U3.id AS idTeller2,
                    U3.name AS naamTeller2,
                    U3.email emailTeller2
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_users U1 on U1.id = W.scheidsrechter_id
                  LEFT JOIN J3_users U2 on U2.id = W.teller1_id
                  LEFT JOIN J3_users U3 on U3.id = W.teller2_id
                  WHERE W.scheidsrechter_id = ? AND
                        W.timestamp >= CURRENT_TIMESTAMP()';
        $params = [$user->id];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToWedstrijden($rows);
    }

    public function GetTelbeurten(Persoon $user): array
    {
        $query = 'SELECT
                    W.id,
                    W.match_id AS matchId,
                    W.timestamp,
                    W.is_veranderd as isVeranderd,
                    U1.id AS scheidsrechterId,
                    U1.name AS scheidsrechter,
                    U1.email emailScheidsrechter,
                    U2.id AS idTeller1,
                    U2.name AS naamTeller1,
                    U2.email AS emailTeller1,
                    U3.id AS idTeller2,
                    U3.name AS naamTeller2,
                    U3.email emailTeller2
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_users U1 on U1.id = W.scheidsrechter_id
                  LEFT JOIN J3_users U2 on U2.id = W.teller1_id
                  LEFT JOIN J3_users U3 on U3.id = W.teller2_id
                  WHERE (M.teller1_id = ? OR W.teller2_id = ?) AND
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
            $scheidsrechter = new Scheidsrechter(
                new Persoon($row->id, $row->naam, $row->email),
                $row->niveau,
                $row->gefloten
            );
            $scheidsrechter->team = $row->teamId != null ? new Team($row->teamnaam, $row->teamId) : null;
            $result[] = $scheidsrechter;
        }
        return $result;
    }

    public function GetWedstrijd(string $matchId): ?Wedstrijd
    {
        $query = 'SELECT
                    W.id,
                    W.match_id AS matchId,
                    W.timestamp,
                    W.is_veranderd as isVeranderd,
                    U1.id AS scheidsrechterId,
                    U1.name AS scheidsrechter,
                    U1.email emailScheidsrechter,
                    U2.id AS idTeller1,
                    U2.name AS naamTeller1,
                    U2.email AS emailTeller1,
                    U3.id AS idTeller2,
                    U3.name AS naamTeller2,
                    U3.email emailTeller2
                  FROM TeamPortal_wedstrijden W
                  LEFT JOIN J3_users U1 on U1.id = W.scheidsrechter_id
                  LEFT JOIN J3_users U2 on U2.id = W.teller1_id
                  LEFT JOIN J3_users U3 on U3.id = W.teller2_id
                  WHERE W.match_id = ?';
        $params = [$matchId];
        $rows = $this->database->Execute($query, $params);
        return count($rows) > 0 ? $this->MapToWedstrijden($rows)[0] : new Wedstrijd($matchId);
    }

    public function GetTellers(): array
    {
        $query = 'SELECT 
                    U.id, 
                    U.name AS naam,
                    U.email,
                    count(W.teller1_id) + count(W.teller2_id) AS geteld, 
                    G.id AS teamId, 
                    G.title AS teamnaam
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  LEFT JOIN TeamPortal_wedstrijden W ON (W.teller1_id = U.id OR W.teller2_id = U.id)
                  WHERE G.parent_id in (SELECT id FROM J3_usergroups WHERE title = "Teams")
                  AND U.id NOT IN (
                    SELECT M.user_id FROM J3_usergroups G
                    INNER JOIN J3_user_usergroup_map M ON G.id = M.group_id
                    WHERE title = "Scheidsrechters"
                  )
                  GROUP BY U.id
                  ORDER BY SUBSTRING(teamnaam, 1, 1), LENGTH(teamnaam), teamnaam, geteld';
        $rows = $this->database->Execute($query);
        $result = [];
        $currentTeam = new Team($rows[0]->teamnaam, $rows[0]->teamId);
        foreach ($rows as $row) {
            if ($currentTeam->GetSkcNaam() !== $row->teamnaam) {
                if ($currentTeam !== null) {
                    $result[] = $currentTeam;
                }
                $currentTeam  = new Team($row->teamnaam, $row->teamId);
            }

            $persoon = new Persoon($row->id, $row->naam, $row->email);
            $persoon->aantalKeerGeteld = $row->geteld;
            $currentTeam->teamgenoten[] = $persoon;
        }
        $result[] = $currentTeam;

        return $result;
    }

    public function Insert(Wedstrijd $wedstrijd): void
    {
        $query = 'INSERT INTO TeamPortal_wedstrijden (match_id, scheidsrechter_id, teller1_id, teller2_id)
                  VALUES (?, ?, ?, ?)';
        $params = [
            $wedstrijd->matchId,
            $wedstrijd->scheidsrechter !== null ? $wedstrijd->scheidsrechter->id : null,
            $wedstrijd->tellers[0] !== null ? $wedstrijd->tellers[0]->id : null,
            $wedstrijd->tellers[1] !== null ? $wedstrijd->tellers[1]->id : null
        ];
        $this->database->Execute($query, $params);
    }

    public function Update(Wedstrijd $wedstrijd): void
    {
        $query = 'UPDATE TeamPortal_wedstrijden
                  SET scheidsrechter_id = ?, teller1_id = ?, teller2_id = ?, is_veranderd = ?, timestamp = ?
                  WHERE match_id = ?';
        $params = [
            $wedstrijd->scheidsrechter !== null ? $wedstrijd->scheidsrechter->id : null,
            $wedstrijd->tellers[0] !== null ? $wedstrijd->tellers[0]->id : null,
            $wedstrijd->tellers[1] !== null ? $wedstrijd->tellers[1]->id : null,
            $wedstrijd->isVeranderd,
            DateFunctions::GetMySqlTimestamp($wedstrijd->timestamp),
            $wedstrijd->matchId
        ];
        $this->database->Execute($query, $params);
    }

    public function Delete(Wedstrijd $wedstrijd): void
    {
        $query = 'DELETE FROM TeamPortal_wedstrijden WHERE match_id = ?';
        $params = [$wedstrijd->matchId];
        $this->database->Execute($query, $params);
    }

    private function MapToWedstrijden(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $newWedstrijd = new Wedstrijd($row->matchId, $row->id);
            $newWedstrijd->timestamp = $row->timestamp !== null ? DateFunctions::CreateDateTime(substr($row->timestamp, 0, 10), substr($row->timestamp, 11, 8)) : null;
            $newWedstrijd->isVeranderd = $row->isVeranderd;
            $newWedstrijd->tellers = [
                $row->idTeller1 ? new Persoon($row->idTeller1, $row->naamTeller1, $row->emailTeller1) : null,
                $row->idTeller2 ? new Persoon($row->idTeller2, $row->naamTeller2, $row->emailTeller2) : null
            ];
            $newWedstrijd->scheidsrechter = $row->scheidsrechterId ? new Persoon($row->scheidsrechterId, $row->scheidsrechter, $row->emailScheidsrechter) : null;

            $result[] = $newWedstrijd;
        }
        return $result;
    }
}
