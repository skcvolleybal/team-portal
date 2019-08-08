<?php

class ZaalwachtGateway
{
    public function __construct($database)
    {
        $this->database = $database;
    }

    public function GetZaalwachtForUserId($userId)
    {
        $query = 'SELECT Z.*, title as team
                  FROM TeamPortal_zaalwacht Z
                  INNER JOIN J3_user_usergroup_map M on Z.team_id = M.group_id
                  INNER JOIN J3_usergroups G ON Z.team_id = G.id
                  WHERE M.user_id = :userId and Z.date >= CURRENT_DATE()';
        $params = [new Param(Column::UserId, $userId, PDO::PARAM_INT)];
        return $this->database->Execute($query, $params);
    }

    public function GetZaalwachtTeams()
    {
        $query = 'SELECT
                    G.title as naam,
                    count(Z.id) as zaalwacht
                  FROM J3_usergroups G
                  LEFT JOIN TeamPortal_zaalwacht Z ON Z.team_id = G.id
                  WHERE G.id in (
                    SELECT id FROM J3_usergroups WHERE parent_id = (
                      SELECT id FROM J3_usergroups WHERE title = \'Teams\'
                    )
                  )
                  GROUP BY G.title
                  ORDER BY zaalwacht, SUBSTRING(naam, 1, 1), LENGTH(naam), naam';
        return $this->database->Execute($query);
    }

    public function GetZaalwachtersWithinPeriod($dagen)
    {
        $query = 'SELECT
                    Z.date,
                    U.name as naam,
                    U.email,
                    G.title as zaalwacht
                  FROM TeamPortal_zaalwacht Z
                  INNER JOIN J3_user_usergroup_map M ON Z.team_id = M.group_id
                  INNER JOIN J3_usergroups G ON M.group_id = G.id
                  INNER JOIN J3_users U ON M.user_id = U.id
                  WHERE date between CURRENT_DATE() AND DATE_ADD(CURRENT_DATE(), INTERVAL :dagen DAY)';
        $params = [new Param(':dagen', $dagen, PDO::PARAM_INT)];
        return $this->database->Execute($query, $params);
    }

    public function GetZaalwachtIndeling()
    {
        $query = 'SELECT
                    Z.date,
                    G.title as team
                  FROM TeamPortal_zaalwacht Z
                  INNER JOIN J3_usergroups G ON Z.team_id = G.id';
        return $this->database->Execute($query);
    }

    public function GetZaalwacht($date)
    {
        $query = 'SELECT
                    id,
                    date,
                    team_id as teamId
                  FROM TeamPortal_zaalwacht WHERE date = :date';
        $params = [new Param(Column::Date, $date, PDO::PARAM_STR)];
        $zaalwachten = $this->database->Execute($query, $params);
        if (count($zaalwachten) == 0) {
            return null;
        }
        return $zaalwachten[0];
    }

    public function Update($zaalwacht, $team)
    {
        $id = $zaalwacht->id;
        $teamId = $team->id;

        $query = 'UPDATE TeamPortal_zaalwacht
                  SET team_id = :teamId
                  WHERE id = :id';
        $params = [
            new Param(':id', $id, PDO::PARAM_INT),
            new Param(':teamId', $teamId, PDO::PARAM_INT),
        ];
        $this->database->Execute($query, $params);
    }

    public function Insert($date, $team)
    {
        $teamId = $team->id;

        $query = 'INSERT INTO TeamPortal_zaalwacht (date, team_id)
                  VALUES (:date, :teamId)';
        $params = [
            new Param(Column::Date, $date, PDO::PARAM_STR),
            new Param(':teamId', $teamId, PDO::PARAM_INT),
        ];
        $this->database->Execute($query, $params);
    }

    public function Delete($zaalwacht)
    {
        $id = $zaalwacht->id;

        $query = 'DELETE FROM TeamPortal_zaalwacht WHERE id = :id';
        $params = [
            new Param(':id', $id, PDO::PARAM_INT),
        ];
        $this->database->Execute($query, $params);
    }
}
