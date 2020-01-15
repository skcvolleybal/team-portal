<?php

class AanwezigheidGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->nevoboGateway = new NevoboGateway();
    }

    public function GetAanwezigheid(int $userId, $matchId, $rol): ?Aanwezigheid
    {
        $query = 'SELECT 
                    A.id,
                    A.match_id as matchId,
                    A.rol,
                    A.is_aanwezig as isAanwezig,
                    U.id as userId,
                    U.name as naam
                  FROM TeamPortal_aanwezigheden A
                  INNER JOIN J3_users U ON A.user_id = U.id
                  WHERE user_id = ? AND match_id = ? AND rol = ?';

        $params = [$userId, $matchId, $rol];
        $rows = $this->database->Execute($query, $params);
        if (count($rows) != 1) {
            return null;
        }

        return new Aanwezigheid(
            $rows[0]->matchId,
            new Persoon($rows[0]->userId, $rows[0]->naam),
            $rows[0]->isAanwezig === "Ja",
            $rows[0]->rol,
            $rows[0]->id
        );
    }

    public function GetAanwezighedenForMatchIds(array $matchIds)
    {
        if (count($matchIds) == 0) {
            return [];
        }

        $inClause  = join(',', array_fill(0, count($matchIds), '?'));
        $query = "SELECT
                    A.id,
                    A.match_id as matchId,
                    A.rol,
                    A.is_aanwezig as isAanwezig,
                    U.id as userId,
                    U.name AS naam,
                    G.id as teamId,
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
        $result = [];
        $rows = $this->database->Execute($query, $params);
        foreach ($rows as $row) {
            $persoon = new Persoon($row->userId, $row->naam);
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

    public function Update(Aanwezigheid $aanwezigheid)
    {
        $query = 'UPDATE TeamPortal_aanwezigheden
                  SET is_aanwezig = ?
                  WHERE id = ?';
        $params = [$aanwezigheid->isAanwezig, $aanwezigheid->id];
        $this->database->Execute($query, $params);
    }

    public function Insert(Aanwezigheid $aanwezigheid)
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

    public function Delete(Aanwezigheid $aanwezigheid)
    {
        $query = 'DELETE FROM TeamPortal_aanwezigheden
                  WHERE id = ?';
        $params = [$aanwezigheid->id];
        $this->database->Execute($query, $params);
    }
}
