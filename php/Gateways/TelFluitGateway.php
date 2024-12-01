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
        $query = "SELECT
        W.id,
        W.match_id AS matchId,
        W.timestamp,
        W.is_veranderd as isVeranderd,
        
        U1.ID AS scheidsrechterId,
        U1.display_name AS scheidsrechter,
        U1.user_email AS emailScheidsrechter,
        
        U2.ID AS idTeller1,
        U2.display_name AS naamTeller1,
        U2.user_email AS emailTeller1,
        
        U3.ID AS idTeller2,
        U3.display_name AS naamTeller2,
        U3.user_email AS emailTeller2
        
    FROM 
        " . $_ENV['DBNAME'] . ".TeamPortal_wedstrijden W
    
    LEFT JOIN 
        " . $_ENV['WPDBNAME'] . ".wp_users U1 on U1.ID = W.scheidsrechter_id
    
    LEFT JOIN 
        " . $_ENV['WPDBNAME'] . ".wp_users U2 on U2.ID = W.teller1_id
    
    LEFT JOIN 
        " . $_ENV['WPDBNAME'] . ".wp_users U3 on U3.ID = W.teller2_id
    
    WHERE 
        (W.scheidsrechter_id = ? OR W.teller1_id = ? OR W.teller2_id = ?) 
        
    AND W.timestamp >= CURRENT_TIMESTAMP()";


        $params = [
            $user->id, $user->id, $user->id
        ];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToWedstrijden($rows);
    }

    public function GetFluitEnTelbeurtenForCalender(Persoon $user): array
    {
        $query = 'SELECT W.timestamp,
                         W.scheidsrechter_id,
                         W.teller1_id,
                         W.teller2_id
                    FROM ' . $_ENV['DBNAME'] . '. teamportal_wedstrijden W
                    WHERE scheidsrechter_id = ? OR teller1_id = ? OR teller2_id = ?';
        $params = [
            $user->id, $user->id, $user->id
        ];

        return $this->database->Execute($query, $params);
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
                  WHERE (W.teller1_id = ? OR W.teller2_id = ?) AND
                        W.timestamp >= CURRENT_TIMESTAMP()';
        $params = [$user->id, $user->id];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToWedstrijden($rows);
    }

    public function GetScheidsrechters(): array
    {
        // Working WordPress query.

        $query = 'SELECT 
        u.ID as id, 
        u.display_name as naam, 
        u.user_email as email,
        MAX(p.ID) as teamId, 
        MAX(p.post_title) as teamnaam,
        COALESCE(COUNT(w.scheidsrechter_id), 0) as gefloten,
        MAX(niveau_meta.meta_value) as niveau,
        MAX(scheidsrechter_dit_seizoen_meta.meta_value) as scheidsrechter_dit_seizoen
        FROM 
        ' . $_ENV['WPDBNAME'] . '.wp_users u
        INNER JOIN 
        ' . $_ENV['WPDBNAME'] . '.wp_usermeta um ON u.ID = um.user_id AND um.meta_key = "team"
        INNER JOIN
        ' . $_ENV['WPDBNAME'] . '.wp_posts p ON p.ID = um.meta_value
        LEFT JOIN
        ' . $_ENV['DBNAME'] . '.TeamPortal_wedstrijden w ON u.ID = w.scheidsrechter_id        
        INNER JOIN
        ' . $_ENV['WPDBNAME'] . '.wp_usermeta niveau_meta ON u.ID = niveau_meta.user_id AND niveau_meta.meta_key = "scheidsrechter" AND niveau_meta.meta_value <> "" AND niveau_meta.meta_value IS NOT NULL
        INNER JOIN
        ' . $_ENV['WPDBNAME'] . '.wp_usermeta scheidsrechter_dit_seizoen_meta ON u.ID = scheidsrechter_dit_seizoen_meta.user_id AND scheidsrechter_dit_seizoen_meta.meta_key = "scheidsrechter_dit_seizoen" AND scheidsrechter_dit_seizoen_meta.meta_value = "1"
        WHERE 
            p.post_type = "team"
        GROUP BY 
            u.ID  
        ORDER BY gefloten DESC';


    //     "SELECT 
    //     u.ID as id, 
    //     u.display_name as naam, 
    //     u.user_email as email,
    //     MAX(p.ID) as teamId, 
    //     MAX(p.post_title) as teamnaam,
    //     COALESCE(COUNT(w.scheidsrechter_id), 0) as gefloten,
    //     MAX(niveau_meta.meta_value) as niveau,
    //     MAX(scheids_meta.meta_value) as scheids_value
    // FROM 
    //     localhost_test.wp_users u
    // INNER JOIN 
    //     localhost_test.wp_usermeta um ON u.ID = um.user_id AND um.meta_key = 'team' 
    // INNER JOIN 
    //     localhost_test.wp_posts p ON p.ID = um.meta_value
    // LEFT JOIN
    //     localhost_test.TeamPortal_wedstrijden w ON u.ID = w.scheidsrechter_id        
    // INNER JOIN
    //     localhost_test.wp_usermeta niveau_meta ON u.ID = niveau_meta.user_id AND niveau_meta.meta_key = 'scheidsrechter' AND niveau_meta.meta_value <> '' AND niveau_meta.meta_value IS NOT NULL
    // -- Add the following INNER JOIN to filter users with 'scheids' checkbox value equal to 1
    // INNER JOIN
    //     localhost_test.wp_usermeta scheids_meta ON u.ID = scheids_meta.user_id AND scheids_meta.meta_key = 'scheids' AND scheids_meta.meta_value = '1'
    // WHERE 
    //     p.post_type = 'team'
    // GROUP BY 
    //     u.ID  
    // ORDER BY gefloten DESC;
    // "

            // Oude Joomla versie
            // $query = 'SELECT
            //             U.id,
            //             U.name AS naam,
            //             U.email,
            //             C.cb_scheidsrechterscode AS niveau,
            //             COUNT(W.scheidsrechter_id) AS gefloten,
            //             teamId,
            //             teamnaam
            //         FROM J3_users U
            //         INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
            //         INNER JOIN J3_usergroups G ON M.group_id = G.id
            //         LEFT JOIN (
            //             SELECT 
            //             user_id, 
            //             group_id AS teamId, 
            //             title AS teamnaam
            //             FROM J3_user_usergroup_map M
            //             INNER JOIN J3_usergroups G ON M.group_id = G.id
            //             WHERE G.parent_id = (SELECT id FROM J3_usergroups WHERE title = \'Teams\')) G2 ON U.id = G2.user_id
            //         LEFT JOIN J3_comprofiler C ON C.user_id = U.id
            //         LEFT JOIN TeamPortal_wedstrijden W ON W.scheidsrechter_id = U.id
            //         WHERE G.id IN (SELECT id FROM J3_usergroups WHERE title = "Scheidsrechters")
            //         GROUP BY U.id, U.name, U.email, C.cb_scheidsrechterscode, teamId, teamnaam
            //         ORDER BY gefloten, naam';


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
                    U1.display_name AS scheidsrechter,
                    U1.user_email emailScheidsrechter,
                    U2.id AS idTeller1,
                    U2.display_name AS naamTeller1,
                    U2.user_email AS emailTeller1,
                    U3.id AS idTeller2,
                    U3.display_name AS naamTeller2,
                    U3.user_email emailTeller2
                  FROM ' . $_ENV['DBNAME'] . '.TeamPortal_wedstrijden W
                  LEFT JOIN ' . $_ENV['WPDBNAME'] . '.wp_users U1 on U1.id = W.scheidsrechter_id
                  LEFT JOIN ' . $_ENV['WPDBNAME'] . '.wp_users U2 on U2.id = W.teller1_id
                  LEFT JOIN ' . $_ENV['WPDBNAME'] . '.wp_users U3 on U3.id = W.teller2_id
                  WHERE W.match_id = ?';
        $params = [$matchId];
        $rows = $this->database->Execute($query, $params);
        return count($rows) > 0 ? $this->MapToWedstrijden($rows)[0] : new Wedstrijd($matchId);
    }

    public function GetTellers(): array
    {

        // Werkende WordPress query 
        // Wel nog goed controleren op test/prod. De data lijkt te kloppen maar niet 100% sure 
        $query = "SELECT
        U.ID as id,
        U.display_name AS naam,
        U.user_email AS email,
        (
            SELECT COUNT(*)
            FROM " . $_ENV['DBNAME'] . ".TeamPortal_wedstrijden W
            WHERE W.teller1_id = U.ID OR W.teller2_id = U.ID
        ) AS geteld,
        P.ID AS teamId,
        P.post_title AS teamnaam
    FROM
        " . $_ENV['WPDBNAME'] . ".wp_users U
    INNER JOIN
        " . $_ENV['WPDBNAME'] . ".wp_usermeta UM ON U.ID = UM.user_id AND UM.meta_key = 'team'
    INNER JOIN
        " . $_ENV['WPDBNAME'] . ".wp_posts P ON UM.meta_value = P.ID AND P.post_type = 'team'
    WHERE
        U.ID NOT IN (
            SELECT user_id
            FROM " . $_ENV['WPDBNAME'] . ".wp_usermeta
            WHERE meta_key = 'scheidsrechter' AND meta_value <> '' AND meta_value IS NOT NULL
        )
    GROUP BY
        U.ID, U.display_name, U.user_email, P.ID, P.post_title";

        // Werkende Joomla query: correcte count 
        // $query = 'SELECT
        // U.id,
        // U.name AS naam,
        // U.email,
        // (SELECT COUNT(*) FROM TeamPortal_wedstrijden W WHERE W.teller1_id = U.id OR W.teller2_id = U.id) AS geteld,
        // G.id AS teamId,
        // G.title AS teamnaam
        // FROM J3_users U
        // INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
        // INNER JOIN J3_usergroups G ON M.group_id = G.id
        // WHERE G.parent_id IN (SELECT id FROM J3_usergroups WHERE title = "Teams")
        // AND U.id NOT IN (
        //         SELECT M.user_id FROM J3_usergroups G
        //         INNER JOIN J3_user_usergroup_map M ON G.id = M.group_id
        //         WHERE title = "Scheidsrechters"
        //     )
        //     GROUP BY U.id, U.name, U.email, G.id, G.title';

        // Oude query onderstaand. Nieuwe query nog niet getest ivm Nevobo RSS feed nog offline.     
        // $query = 'SELECT 
        //             U.id, 
        //             U.name AS naam,
        //             U.email,
        //             COUNT(W1.teller1_id) + COUNT(W2.teller2_id) AS geteld, 
        //             G.id AS teamId, 
        //             G.title AS teamnaam
        //           FROM J3_users U
        //           INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
        //           INNER JOIN J3_usergroups G ON M.group_id = G.id
        //           LEFT JOIN TeamPortal_wedstrijden W1 ON (W1.teller1_id = U.id)
        //           LEFT JOIN TeamPortal_wedstrijden W2 ON (W2.teller2_id = U.id)
        //           WHERE G.parent_id in (SELECT id FROM J3_usergroups WHERE title = "Teams")
        //           AND U.id NOT IN (
        //             SELECT M.user_id FROM J3_usergroups G
        //             INNER JOIN J3_user_usergroup_map M ON G.id = M.group_id
        //             WHERE title = "Scheidsrechters"
        //           )
        //           GROUP BY U.id, U.name, U.email, G.id, G.title, W1.teller1_id, W2.teller2_id
        //           ORDER BY SUBSTRING(teamnaam, 1, 1), LENGTH(teamnaam), teamnaam, geteld;';
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
            $persoon->spelertijd = 'abc';
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
                  SET 
                    scheidsrechter_id = ?, 
                    teller1_id = ?, 
                    teller2_id = ?, 
                    is_veranderd = ?, 
                    timestamp = ?
                  WHERE match_id = ?';
        $params = [
            $wedstrijd->scheidsrechter !== null ? $wedstrijd->scheidsrechter->id : null,
            $wedstrijd->tellers[0] !== null ? $wedstrijd->tellers[0]->id : null,
            $wedstrijd->tellers[1] !== null ? $wedstrijd->tellers[1]->id : null,
            $wedstrijd->isVeranderd ? 1 : 0,
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
