<?php

class ZaalwachtGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetZaalwachtenOfUser(int $userId): array
    {
        $query = 'SELECT 
                    Z.id,
                    Z.team_id as teamId, 
                    Z.date,
                    title as teamnaam
                  FROM TeamPortal_zaalwacht Z
                  INNER JOIN J3_user_usergroup_map M on Z.team_id = M.group_id
                  INNER JOIN J3_usergroups G ON Z.team_id = G.id
                  WHERE M.user_id = ? and Z.date >= CURRENT_DATE()';
        $params = [$userId];
        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Zaalwacht(
                $row->date,
                $row->id,
                new Team($row->teamnaam, $row->teamId)
            );
        }
        return $result;
    }

    public function GetZaalwachtSamenvatting(): array
    {
        $query = 'SELECT
                    G.id as teamId,
                    G.title as teamnaam,
                    count(Z.id) as aantal
                  FROM J3_usergroups G
                  LEFT JOIN TeamPortal_zaalwacht Z ON Z.team_id = G.id
                  WHERE G.id in (
                    SELECT id FROM J3_usergroups WHERE parent_id = (
                      SELECT id FROM J3_usergroups WHERE title = \'Teams\'
                    )
                  )
                  GROUP BY G.title
                  ORDER BY aantal, SUBSTRING(teamnaam, 1, 1), LENGTH(teamnaam), teamnaam';
        $rows = $this->database->Execute($query);
        $result = [];
        foreach ($rows as $row) {
            $result[] = (object) [
                'team' => new Team($row->teamnaam, $row->teamId),
                'aantal' => $row->aantal
            ];
        }
        return $result;
    }

    public function GetZaalwachtIndeling(): array
    {
        $query = 'SELECT
                    Z.date,
                    G.id as teamId,
                    G.title as team
                  FROM TeamPortal_zaalwacht Z
                  INNER JOIN J3_usergroups G ON Z.team_id = G.id';
        $rows = $this->database->Execute($query);

        $response = [];
        foreach ($rows as $row) {
            $rows[$row->date] = new Team($row->team, $row->teamId);
        }
        return $response;
    }

    public function GetZaalwacht(DateTime $date): ?Zaalwacht
    {
        $query = 'SELECT
                    Z.id,
                    date,
                    team_id as teamId,
                    G.title as teamnaam
                  FROM TeamPortal_zaalwacht Z
                  INNER JOIN J3_usergroups G ON Z.team_id = G.id
                  WHERE date = ?';
        $params = [DateFunctions::GetYmdNotation($date)];
        $result = $this->database->Execute($query, $params);
        if (count($result) != 1) {
            return null;
        }
        return new Zaalwacht(
            DateFunctions::CreateDateTime($result[0]->date),
            $result[0]->id,
            new Team($result[0]->teamnaam, $result[0]->teamId)
        );
    }

    public function Update(Zaalwacht $zaalwacht)
    {
        $query = 'UPDATE TeamPortal_zaalwacht
                  SET team_id = ?
                  WHERE id = ?';
        $params = [$zaalwacht->team->id, $zaalwacht->id];
        $this->database->Execute($query, $params);
    }

    public function Insert(Zaalwacht $zaalwacht)
    {
        $query = 'INSERT INTO TeamPortal_zaalwacht (date, team_id)
                  VALUES (?, ?)';
        $params = [
            DateFunctions::GetYmdNotation($zaalwacht->date),
            $zaalwacht->team->id
        ];
        $this->database->Execute($query, $params);
    }

    public function Delete(Zaalwacht $zaalwacht)
    {
        $query = 'DELETE FROM TeamPortal_zaalwacht WHERE id = ?';
        $params = [$zaalwacht->id];
        $this->database->Execute($query, $params);
    }
}
