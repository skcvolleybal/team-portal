<?php

namespace TeamPortal\Gateways;

use DateTime;
use TeamPortal\Common\Database;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Team;
use TeamPortal\Entities\Zaalwacht;

class ZaalwachtGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetZaalwachtenOfUser(Persoon $user): array 
    {
        $query = "SELECT 
        Z.id,
        Z.date,
        team1_id AS eersteZaalwachtId,
        MAX(p1.post_title) AS eersteZaalwacht,
        team2_id AS tweedeZaalwachtId,
        MAX(p2.post_title) AS tweedeZaalwacht
    FROM 
        " . $_ENV['DBNAME'] . ".TeamPortal_zaalwacht Z
    
    LEFT JOIN 
        " . $_ENV['WPDBNAME'] . ".wp_usermeta um1 ON Z.team1_id = um1.meta_value AND um1.meta_key = 'team'
    LEFT JOIN 
        " . $_ENV['WPDBNAME'] . ".wp_posts p1 ON um1.user_id = p1.ID AND p1.post_type = 'team'
    
    LEFT JOIN 
        " . $_ENV['WPDBNAME'] . ".wp_usermeta um2 ON Z.team2_id = um2.meta_value AND um2.meta_key = 'team'
    LEFT JOIN 
        " . $_ENV['WPDBNAME'] . ".wp_posts p2 ON um2.user_id = p2.ID AND p2.post_type = 'team'
    
    WHERE 
        Z.date >= CURRENT_DATE() 
        AND (um1.user_id = ? OR um2.user_id = ?)
    
    GROUP BY 
        Z.id"; 


        // Oude Joomla query
        // $query = 'SELECT 
        //             Z.id,
        //             Z.date,
        //             team1_id AS eersteZaalwachtId,
        //             G1.title AS eersteZaalwacht,
        //             team2_id AS tweedeZaalwachtId,
        //             G2.title AS tweedeZaalwacht
        //           FROM TeamPortal_zaalwacht Z
        //           LEFT JOIN J3_user_usergroup_map M1 on Z.team1_id = M1.group_id
        //           LEFT JOIN J3_usergroups G1 ON Z.team1_id = G1.id
        //           LEFT JOIN J3_user_usergroup_map M2 on Z.team2_id = M2.group_id
        //           LEFT JOIN J3_usergroups G2 ON Z.team2_id = G2.id
        //           WHERE Z.date >= CURRENT_DATE() AND (M1.user_id = ? OR M2.user_id = ?)';
        $params = [$user->id, $user->id];
        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[] = $this->MapToZaalwacht($row);
        }
        return $result;
    }

    public function GetZaalwachtSamenvatting(): array 
    {
        $query = "SELECT
        p.ID AS teamId,
        p.post_title AS teamnaam,
        count(Z.id) AS aantal
        FROM 
            " . $_ENV['WPDBNAME'] . ".wp_posts p
            
        LEFT JOIN (
            SELECT id, date, team1_id AS team_id
            FROM " . $_ENV['DBNAME'] . ".TeamPortal_zaalwacht WHERE team1_id IS NOT NULL
            UNION
            SELECT id, date, team2_id AS team_id
            FROM " . $_ENV['DBNAME'] . ".TeamPortal_zaalwacht WHERE team2_id IS NOT NULL
        ) Z ON Z.team_id = p.ID
        
        WHERE 
            p.post_type = 'team'
            
        GROUP BY 
            p.ID, p.post_title
        
        ORDER BY 
            aantal, 
            SUBSTRING(p.post_title, 1, 1), 
            LENGTH(p.post_title), 
            p.post_title
        ";
        $rows = $this->database->Execute($query);
        $result = [];
        foreach ($rows as $row) {
            $team = new Team($row->teamnaam, $row->teamId);
            $team->aantalZaalwachten = $row->aantal;

            $result[] = $team;
        }
        return $result;
    }

    public function GetZaalwachtIndeling(): array
    {
        $query = 'SELECT
                    Z.date,
                    team1_id AS eersteZaalwachtId,
                    G1.title AS eersteZaalwacht,
                    team2_id AS tweedeZaalwachtId,
                    G2.title AS tweedeZaalwacht
                  FROM TeamPortal_zaalwacht Z
                  INNER JOIN J3_usergroups G ON Z.team_id = G.id
                  INNER JOIN J3_usergroups G2 ON Z.team_id = G2.id';
        $rows = $this->database->Execute($query);

        $response = [];
        foreach ($rows as $row) {
            $rows[$row->date] = new Team($row->team, $row->teamId);
        }
        return $response;
    }

    public function GetZaalwacht(DateTime $date): ?Zaalwacht
    {

        $query = "
            SELECT 
            Z.id,
            date,
            team1_id AS eersteZaalwachtId,
            p1.post_title AS eersteZaalwacht,
            team2_id AS tweedeZaalwachtId,
            p2.post_title AS tweedeZaalwacht
                
            FROM 
            " . $_ENV['DBNAME'] . ".TeamPortal_zaalwacht Z
                
            left join " . $_ENV['WPDBNAME'] . ".wp_posts P1 on Z.team1_id = P1.id
            left join " . $_ENV['WPDBNAME'] . ".wp_posts P2 on Z.team2_id = P2.id

            WHERE 
                date = ?";

        // Oude Joomla query
        // $query = 'SELECT
        //             Z.id,
        //             date,
        //             team1_id AS eersteZaalwachtId,
        //             G1.title AS eersteZaalwacht,
        //             team2_id AS tweedeZaalwachtId,
        //             G2.title AS tweedeZaalwacht
        //           FROM TeamPortal_zaalwacht Z
        //           LEFT JOIN J3_usergroups G1 ON Z.team1_id = G1.id
        //           LEFT JOIN J3_usergroups G2 ON Z.team2_id = G2.id
        //           WHERE date = ?';
        $params = [DateFunctions::GetYmdNotation($date)];
        $rows = $this->database->Execute($query, $params);
        if (count($rows) != 1) {
            return null;
        }
        return $this->MapToZaalwacht($rows[0]);
    }

    public function Update(Zaalwacht $zaalwacht): void
    {
        $query = 'UPDATE TeamPortal_zaalwacht
                  SET team1_id = ?, team2_id = ?
                  WHERE id = ?';
        $params = [
            $zaalwacht->eersteZaalwacht ? $zaalwacht->eersteZaalwacht->id : null,
            $zaalwacht->tweedeZaalwacht ? $zaalwacht->tweedeZaalwacht->id : null,
            $zaalwacht->id
        ];
        $this->database->Execute($query, $params);
    }

    public function Insert(Zaalwacht $zaalwacht): void
    {
        $query = 'INSERT INTO TeamPortal_zaalwacht (date, team1_id, team2_id)
                  VALUES (?, ?, ?)';
        $params = [
            DateFunctions::GetYmdNotation($zaalwacht->date),
            $zaalwacht->eersteZaalwacht ? $zaalwacht->eersteZaalwacht->id : null,
            $zaalwacht->tweedeZaalwacht ? $zaalwacht->tweedeZaalwacht->id : null
        ];
        $this->database->Execute($query, $params);
    }

    public function Delete(Zaalwacht $zaalwacht): void
    {
        $query = 'DELETE FROM TeamPortal_zaalwacht WHERE id = ?';
        $params = [$zaalwacht->id];
        $this->database->Execute($query, $params);
    }

    private function MapToZaalwacht($row): Zaalwacht
    {
        return new Zaalwacht(
            $row->id,
            DateFunctions::CreateDateTime($row->date),
            $row->eersteZaalwachtId !== null ? new Team($row->eersteZaalwacht, $row->eersteZaalwachtId) : null,
            $row->tweedeZaalwachtId !== null ? new Team($row->tweedeZaalwacht, $row->tweedeZaalwachtId) : null,
        );
    }
}
