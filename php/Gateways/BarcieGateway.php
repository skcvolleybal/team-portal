<?php

include_once 'NevoboGateway.php';

class BarcieGateway
{
    public function __construct($database)
    {
        $this->database = $database;
        $this->nevoboGateway = new NevoboGateway();
    }

    public function GetDateId($date)
    {
        $query = 'SELECT id
                  FROM barcie_days
                  WHERE date = :date';
        $params = [
            new Param(Column::Date, $date, PDO::PARAM_STR),
        ];

        $result = $this->database->Execute($query, $params);
        if (count($result) == 0) {
            return null;
        }
        return $result[0]->id;
    }

    public function GetBarcieDagen()
    {
        $query = 'SELECT *
                  FROM barcie_days
                  WHERE CURRENT_DATE() <= date';
        return $this->database->Execute($query);
    }

    public function AddBarcieDag($date)
    {
        $query = 'INSERT INTO barcie_days (date)
                  VALUES (:date)';
        $params = [
            new Param(Column::Date, $date, PDO::PARAM_STR),
        ];

        return $this->database->Execute($query, $params);
    }

    public function GetBeschikbaarheden($userId)
    {
        $query = 'SELECT D.date, A.is_beschikbaar
                  FROM barcie_availability A
                  INNER JOIN barcie_days D on A.day_id = D.id
                  WHERE A.user_id = :userId and D.date >= CURRENT_DATE()';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
        ];

        return $this->database->Execute($query, $params);
    }

    public function GetBeschikbaarhedenForDate($date)
    {
        $query = 'SELECT
                    A.user_id as userId,
                    A.is_beschikbaar
                  FROM barcie_availability A
                  INNER JOIN barcie_days D on A.day_id = D.id
                  WHERE D.date = :date';
        $params = [
            new Param(Column::Date, $date, PDO::PARAM_STR),
        ];

        return $this->database->Execute($query, $params);
    }

    public function GetBeschikbaarheid($userId, $dayId)
    {
        $query = 'SELECT *
                  FROM barcie_availability
                  WHERE user_id = :userId and day_id = :dayId';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::dayId, $dayId, PDO::PARAM_INT),
        ];

        $result = $this->database->Execute($query, $params);
        if (count($result) == 0) {
            return null;
        }
        return $result[0];
    }

    public function UpdateBeschikbaarheid($id, $beschikbaarheid)
    {
        $this->CheckBeschikbaarheid($beschikbaarheid);
        $query = 'UPDATE barcie_availability
                  SET beschikbaarheid = :beschikbaarheid
                  WHERE id = :id';
        $params = [
            new Param(':id', $id, PDO::PARAM_INT),
            new Param(Column::IsBeschikbaar, $beschikbaarheid, PDO::PARAM_STR),
        ];

        return $this->database->Execute($query, $params);
    }

    public function InsertBeschikbaarheid($userId, $dayId, $isBeschikbaar)
    {
        $this->CheckBeschikbaarheid($isBeschikbaar);
        $query = 'INSERT INTO barcie_availability (day_id, user_id, is_beschikbaar)
                  VALUES (:dayId, :userId, :isBeschikbaar)';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::DayId, $dayId, PDO::PARAM_INT),
            new Param(Column::IsBeschikbaar, $isBeschikbaar, PDO::PARAM_STR),
        ];

        return $this->database->Execute($query, $params);
    }

    private function CheckBeschikbaarheid($beschikbaarheid)
    {
        if (!in_array($beschikbaarheid, ['Ja', 'Nee', 'Onbekend'])) {
            throw new InvalidArgumentException('$beschikbaarheid is niet een van de opties');
        }
    }

    public function SetBHV($id, $isBHV)
    {
        $isBHV = $isBHV ? 1 : 0;
        $query = 'UPDATE barcie_availability
                  SET is_bhv = :isBHV
                  WHERE id = :id';
        $params = [
            new Param(':isBHV', $isBHV, PDO::PARAM_INT),
            new Param(':id', $id, PDO::PARAM_INT)
        ];

        return $this->database->Execute($query, $params);
    }

    public function GetAanwezigheid($dayId, $userId, $shift)
    {
        $query = 'SELECT * FROM barcie_schedule_map
                  WHERE day_id = :dayId and
                        user_id = :userId and
                        shift = :shift';
        $params = [
            new Param(Column::dayId, $dayId, PDO::PARAM_INT),
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(':shift', $shift, PDO::PARAM_INT),
        ];

        $aanwezigheden = $this->database->Execute($query, $params);
        if (count($aanwezigheden) == 0) {
            return null;
        }
        return $aanwezigheden[0];
    }

    public function GetBarcieAanwezigheden()
    {
        $query = 'SELECT
                    M.user_id as userId,
                    M.name as naam,
                    M.is_bhv as isBhv,
                    D.date,
                    M.shift
                  FROM barcie_days D
                  LEFT JOIN (
                    SELECT
                        M.day_id,
                        M.shift,
                        M.user_id,
                        U.name,
                        M.is_bhv
                    FROM barcie_schedule_map M
                    INNER JOIN J3_users U on U.id = M.user_id
                  ) M on M.day_id = D.id
                  WHERE D.date >= CURRENT_DATE()
                  ORDER BY date, shift, name';
        return $this->database->Execute($query);
    }

    public function GetBarcieRoosterForNextWeek()
    {
        $query = 'SELECT
                    D.date,
                    U.id as userId,
                    U.name as naam,
                    U.email,
                    shift,
                    is_bhv as isBhv
                  FROM barcie_schedule_map M
                  INNER JOIN J3_users U ON M.user_id = U.id
                  INNER JOIN barcie_days D ON M.day_id = D.id
                  WHERE D.date BETWEEN CURRENT_DATE() and DATE_ADD(CURRENT_DATE(), INTERVAL 7 DAY)';
        return $this->database->Execute($query);
    }

    public function GetBarcieLeden()
    {
        $query = 'SELECT
                    U.id,
                    U.name AS naam,
                    count(B.id) AS aantalDiensten
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON G.id = M.group_id
                  LEFT JOIN barcie_schedule_map B ON B.user_id = U.id
                  WHERE title = \'Barcie\'
                  GROUP BY U.id
                  ORDER BY count(B.id) ASC';
        return $this->database->Execute($query);
    }

    public function GetBarcielidByName($name)
    {
        $query = 'SELECT
                    U.id,
                    U.name AS naam
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON G.id = M.group_id                  
                  WHERE title = \'Barcie\' and
                        U.name = :name';
        $params = [
            new Param(':name', $name, PDO::PARAM_INT)
        ];
        return $this->database->Execute($query, $params);
    }

    public function InsertAanwezigheid($dayId, $userId, $shift)
    {
        $query = 'INSERT INTO barcie_schedule_map (day_id, user_id, shift)
                  VALUES (:dayId, :userId, :shift)';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::dayId, $dayId, PDO::PARAM_INT),
            new Param(':shift', $shift, PDO::PARAM_INT),
        ];

        return $this->database->Execute($query, $params);
    }

    public function DeleteAanwezigheid($id)
    {
        $query = 'DELETE FROM barcie_schedule_map
                  WHERE id = :id';
        $params = [
            new Param(':id', $id, PDO::PARAM_INT),
        ];

        $this->database->Execute($query, $params);
    }

    public function DeleteBarcieDay($id)
    {
        $query = 'DELETE FROM barcie_days
                  WHERE id = :id';
        $params = [
            new Param(':id', $id, PDO::PARAM_INT),
        ];

        $this->database->Execute($query, $params);
    }

    public function ToggleBhv($id)
    {
        $query = 'UPDATE barcie_schedule_map
                  SET is_bhv =  IF(is_bhv = 1, 0, 1)
                  WHERE id = :id';
        $params = [
            new Param(':id', $id, PDO::PARAM_INT),
        ];

        $this->database->Execute($query, $params);
    }
}
