<?php

namespace TeamPortal\Gateways;

use TeamPortal\Common\Database;
use TeamPortal\Entities\Aanwezigheid;
use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Team;

class AanwezigheidGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetAanwezigheid(Persoon $user, string $matchId, string $rol): Aanwezigheid
    {
        $query = 'SELECT 
                    A.id,
                    A.match_id AS matchId,
                    A.rol,
                    A.is_aanwezig AS isAanwezig,
                    U.id AS userId,
                    U.name AS naam,
                    U.email,
                    T.teamId,
                    T.teamnaam
                  FROM TeamPortal_aanwezigheden A
                  INNER JOIN J3_users U ON A.user_id = U.id
                  LEFT JOIN (
                    SELECT 
                        M.user_id as userId, 
                        G.id as teamId, 
                        G.title as teamnaam
                    FROM J3_user_usergroup_map M
                    INNER JOIN J3_usergroups G ON G.id = M.group_id
                    WHERE G.id = ?
                  ) T ON U.id = T.userId
                  WHERE A.user_id = ? AND match_id = ? AND rol = ?';
        $params = [
            $user->team ? $user->team->id : null,
            $user->id,
            $matchId,
            $rol
        ];
        $rows = $this->database->Execute($query, $params);
        if (count($rows) == 0) {
            return new Aanwezigheid($matchId, $user, null, $rol);
        }
        return $this->MapToAanwezigheden($rows)[0];
    }

    public function GetAanwezighedenForMatchIds(array $matchIds): ?array
    {
        if (count($matchIds) == 0) {
            return [];
        }

        $inClause  = join(',', array_fill(0, count($matchIds), '?'));
        $query = "SELECT
                    A.id,
                    A.match_id AS matchId,
                    A.rol,
                    A.is_aanwezig AS isAanwezig,
                    U.id AS userId,
                    U.name AS naam,
                    U.email,
                    G.id AS teamId,
                    G.title AS teamnaam
                  FROM TeamPortal_aanwezigheden A
                  INNER JOIN J3_users U ON A.user_id = U.id
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  WHERE
                    A.match_id IN ($inClause) AND
                    (
                        ((G.title LIKE 'Heren %' OR G.title LIKE 'Dames %') AND A.rol = 'speler') OR
                        (G.title LIKE 'Coach %' AND A.rol = 'coach')
                    )
                  ORDER BY naam";
        $params = $matchIds;
        $rows = $this->database->Execute($query, $params);
        return $this->MapToAanwezigheden($rows);
    }

    public function Update(Aanwezigheid $aanwezigheid): void
    {
        $query = 'UPDATE TeamPortal_aanwezigheden
                  SET is_aanwezig = ?
                  WHERE id = ?';
        $params = [$aanwezigheid->isAanwezig, $aanwezigheid->id];
        $this->database->Execute($query, $params);
    }

    public function Insert(Aanwezigheid $aanwezigheid): void
    {
        $query = 'INSERT INTO TeamPortal_aanwezigheden (user_id, match_id, is_aanwezig, rol)
                  VALUES (?, ?, ?, ?)';
        $params = [
            $aanwezigheid->persoon->id,
            $aanwezigheid->matchId,
            $aanwezigheid->isAanwezig,
            $aanwezigheid->rol
        ];
        $this->database->Execute($query, $params);
    }

    public function Delete(Aanwezigheid $aanwezigheid): void
    {
        $query = 'DELETE FROM TeamPortal_aanwezigheden WHERE id = ?';
        $params = [$aanwezigheid->id];
        $this->database->Execute($query, $params);
    }

    private function MapToAanwezigheden(array $rows)
    {
        $result = [];
        foreach ($rows as $row) {
            $persoon = new Persoon(
                $row->userId,
                $row->naam,
                $row->email
            );
            if ($row->teamId) {
                $persoon->team = new Team($row->teamnaam, $row->teamId);
            }

            $result[] = new Aanwezigheid(
                $row->matchId,
                $persoon,
                $row->isAanwezig === "Ja",
                $row->rol,
                $row->id
            );
        }

        return $result;
    }
}
