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

    public function GetAanwezigheid(
        Persoon $user,
        string $matchId,
        string $rol,
    ): Aanwezigheid {
        $query =
            'SELECT
                    A.id,
                    A.match_id AS matchId,
                    A.rol,
                    A.is_aanwezig AS isAanwezig,
                    U.ID AS userId,
                    U.display_name AS naam,
                    U.user_email AS email,
                    P.ID AS teamId,
                    P.post_title AS teamnaam
                  FROM ' .
            $_ENV["DBNAME"] .
            '.TeamPortal_aanwezigheden A
                  INNER JOIN ' .
            $_ENV["WPDBNAME"] .
            '.wp_users U ON A.user_id = U.ID
                  LEFT JOIN ' .
            $_ENV["WPDBNAME"] .
            '.wp_usermeta UM ON UM.user_id = U.ID AND UM.meta_key = \'team\'
                  LEFT JOIN ' .
            $_ENV["WPDBNAME"] .
            '.wp_posts P ON P.ID = UM.meta_value AND P.post_type = \'team\'
                  WHERE A.user_id = ? AND A.match_id = ? AND A.rol = ?';
        $params = [$user->id, $matchId, $rol];
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

        $inClause = join(",", array_fill(0, count($matchIds), "?"));
        $query =
            "SELECT
                    A.id,
                    A.match_id AS matchId,
                    A.rol,
                    A.is_aanwezig AS isAanwezig,
                    U.ID AS userId,
                    U.display_name AS naam,
                    U.user_email AS email,
                    P.ID AS teamId,
                    P.post_title AS teamnaam
                  FROM " .
            $_ENV["DBNAME"] .
            ".TeamPortal_aanwezigheden A
                  INNER JOIN " .
            $_ENV["WPDBNAME"] .
            ".wp_users U ON A.user_id = U.ID
                  INNER JOIN " .
            $_ENV["WPDBNAME"] .
            ".wp_usermeta UM ON UM.user_id = U.ID AND UM.meta_key = 'team'
                  INNER JOIN " .
            $_ENV["WPDBNAME"] .
            ".wp_posts P ON P.ID = UM.meta_value AND P.post_type = 'team'
                  WHERE
                    A.match_id IN ($inClause) AND
                    (P.post_title LIKE 'Heren %' OR P.post_title LIKE 'Dames %') AND
                    A.rol = 'speler'
                  UNION
                  SELECT
                    A.id,
                    A.match_id AS matchId,
                    A.rol,
                    A.is_aanwezig AS isAanwezig,
                    U.ID AS userId,
                    U.display_name AS naam,
                    U.user_email AS email,
                    null AS teamId,
                    null AS teamnaam
                  FROM " .
            $_ENV["DBNAME"] .
            ".TeamPortal_aanwezigheden A
                  INNER JOIN " .
            $_ENV["WPDBNAME"] .
            ".wp_users U ON A.user_id = U.ID
                  INNER JOIN " .
            $_ENV["WPDBNAME"] .
            ".wp_usermeta UM ON UM.user_id = U.ID AND UM.meta_key = 'coach_van'
                  WHERE
                    A.match_id IN ($inClause) AND
                    A.rol = 'coach'
                  ORDER BY naam";
        $params = array_merge($matchIds, $matchIds);
        $rows = $this->database->Execute($query, $params);
        return $this->MapToAanwezigheden($rows);
    }

    public function Update(Aanwezigheid $aanwezigheid): void
    {
        $query =
            "UPDATE " .
            $_ENV["DBNAME"] .
            '.TeamPortal_aanwezigheden
                  SET is_aanwezig = ?
                  WHERE id = ?';
        $params = [
            $aanwezigheid->isAanwezig === null
                ? null
                : ($aanwezigheid->isAanwezig
                    ? "Ja"
                    : "Nee"),
            $aanwezigheid->id,
        ];
        $this->database->Execute($query, $params);
    }

    public function Insert(Aanwezigheid $aanwezigheid): void
    {
        $query =
            "INSERT INTO " .
            $_ENV["DBNAME"] .
            '.TeamPortal_aanwezigheden (user_id, match_id, is_aanwezig, rol)
                  VALUES (?, ?, ?, ?)';
        $params = [
            $aanwezigheid->persoon->id,
            $aanwezigheid->matchId,
            $aanwezigheid->isAanwezig === null
                ? null
                : ($aanwezigheid->isAanwezig
                    ? "Ja"
                    : "Nee"),
            $aanwezigheid->rol,
        ];
        $this->database->Execute($query, $params);
    }

    public function Delete(Aanwezigheid $aanwezigheid): void
    {
        $query =
            "DELETE FROM " .
            $_ENV["DBNAME"] .
            ".TeamPortal_aanwezigheden WHERE id = ?";
        $params = [$aanwezigheid->id];
        $this->database->Execute($query, $params);
    }

    private function MapToAanwezigheden(array $rows)
    {
        $result = [];
        foreach ($rows as $row) {
            $persoon = new Persoon($row->userId, $row->naam, $row->email);
            if ($row->teamId) {
                $persoon->team = new Team($row->teamnaam, $row->teamId);
            }

            $result[] = new Aanwezigheid(
                $row->matchId,
                $persoon,
                $row->isAanwezig === "Ja",
                $row->rol,
                $row->id,
            );
        }

        return $result;
    }
}
