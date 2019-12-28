<?php

class AanwezigheidGateway
{
    public function __construct($database)
    {
        $this->database = $database;
        $this->nevoboGateway = new NevoboGateway();
    }

    public function GetAanwezigheid($userId, $matchId, $rol)
    {
        $query = 'SELECT *
                  FROM TeamPortal_aanwezigheden
                  WHERE user_id = ? AND match_id = ? AND rol = ?';

        $params = [$userId, $matchId, $rol];
        $result = $this->database->Execute2($query, $params);
        if (count($result) > 0) {
            return $result[0];
        }
        return null;
    }

    public function GetAanwezighedenForMatchIds($matchIds)
    {
        $inClause  = join(',', array_fill(0, count($matchIds), '?'));
        $query = "SELECT
                    A.*,
                    U.name AS naam,
                    G.title AS team
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
        return $this->database->Execute2($query, $params);
    }

    public function Update($id, $isAanwezig)
    {
        $query = 'UPDATE TeamPortal_aanwezigheden
                  SET is_aanwezig = ?
                  WHERE id = ?';
        $params = [$isAanwezig, $id];
        $this->database->Execute2($query, $params);
    }

    public function Insert($userId, $matchId, $isAanwezig, $rol)
    {
        $query = 'INSERT INTO TeamPortal_aanwezigheden (user_id, match_id, is_aanwezig, rol)
                  VALUES (?, ?, ?, ?)';
        $params = [$userId, $matchId, $isAanwezig, $rol];
        $this->database->Execute2($query, $params);
    }

    public function Delete($id)
    {
        $query = 'DELETE FROM TeamPortal_aanwezigheden
                  WHERE id = ?';
        $params = [$id];
        $this->database->Execute2($query, $params);
    }
}
