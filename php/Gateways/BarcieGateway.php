<?php

class BarcieGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
        $this->nevoboGateway = new NevoboGateway();
    }

    public function GetDateId(DateTime $date): ?int
    {
        $query = 'SELECT id
                  FROM barcie_days
                  WHERE date = ?';
        $params = [DateFunctions::GetYmdNotation($date)];

        $result = $this->database->Execute($query, $params);
        if (count($result) != 1) {
            return null;
        }
        return $result[0]->id;
    }

    public function GetBarciedagen(): array
    {
        $query = 'SELECT id, date, remarks
                  FROM barcie_days
                  WHERE CURRENT_DATE() <= date';
        $rows = $this->database->Execute($query);
        $result = [];
        foreach ($rows as $row) {
            $result[] = DateFunctions::CreateDateTime($row->date);
        }
        return $result;
    }

    public function AddBarcieDag(DateTime $date)
    {
        $query = 'INSERT INTO barcie_days (date)
                  VALUES (?)';
        $params = [DateFunctions::GetYmdNotation($date)];

        $this->database->Execute($query, $params);
    }

    public function GetBeschikbaarheden(Persoon $persoon)
    {
        $query = 'SELECT 
                    A.id,
                    U.id as userId,
                    U.name as naam,
                    D.date, 
                    A.is_beschikbaar as isBeschikbaar
                  FROM barcie_availability A
                  INNER JOIN J3_users U ON A.user_id = U.id
                  INNER JOIN barcie_days D on A.day_id = D.id
                  WHERE A.user_id = ? and D.date >= CURRENT_DATE()';
        $params = [$persoon->id];

        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Beschikbaarheid(
                $row->id,
                new Persoon($row->userId, $row->naam),
                DateFunctions::CreateDateTime($row->date),
                $row->isBeschikbaar == "Ja"
            );
        }
        return $result;
    }

    public function GetBeschikbaarhedenForDate(DateTime $date): array
    {
        $query = 'SELECT
                    A.id,
                    U.id as userId,
                    U.name as naam,
                    A.is_beschikbaar as isBeschikbaar
                  FROM barcie_availability A
                  INNER JOIN J3_users U ON A.user_id = U.id
                  INNER JOIN barcie_days D on A.day_id = D.id
                  WHERE D.date = ?';
        $params = [DateFunctions::GetYmdNotation($date)];

        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Beschikbaarheid(
                $row->id,
                new Persoon($row->userId, $row->naam),
                $date,
                $row->isBeschikbaar == "Ja"
            );
        }
        return $result;
    }

    public function GetBeschikbaarheid(int $userId, int $dayId)
    {
        $query = 'SELECT
                    A.id,
                    U.id as userId,
                    U.name as naam,
                    D.date, 
                    A.is_beschikbaar as isBeschikbaar
                  FROM barcie_availability A
                  INNER JOIN barcie_days D ON A.day_id = D.id
                  INNER JOIN J3_users U ON A.user_id = U.id
                  WHERE user_id = ? and day_id = ?';
        $params = [$userId, $dayId];

        $rows = $this->database->Execute($query, $params);
        if (count($rows) != 1) {
            return null;
        }

        return $result[] = new Beschikbaarheid(
            $rows[0]->id,
            new Persoon($rows[0]->userId, $rows[0]->naam),
            DateFunctions::CreateDateTime($rows[0]->date),
            $rows[0]->isBeschikbaar == "Ja"
        );;
    }

    public function UpdateBeschikbaarheid(Beschikbaarheid $beschikbaarheid)
    {
        $query = 'UPDATE barcie_availability
                  SET is_beschikbaar = ?
                  WHERE id = ?';
        $params = [
            $beschikbaarheid->isBeschikbaar ? "Ja" : "Nee",
            $beschikbaarheid->id
        ];

        return $this->database->Execute($query, $params);
    }

    public function DeleteBeschikbaarheid(Beschikbaarheid $beschikbaarheid)
    {
        $query = 'DELETE FROM barcie_availability WHERE id = ?';
        $params = [$beschikbaarheid->id];

        return $this->database->Execute($query, $params);
    }

    public function InsertBeschikbaarheid(Beschikbaarheid $beschikbaarheid, int $dayId)
    {
        $query = 'INSERT INTO barcie_availability (day_id, user_id, is_beschikbaar)
                  VALUES (?, ?, ?)';
        $params = [
            $dayId,
            $beschikbaarheid->persoon->id,
            $beschikbaarheid->isBeschikbaar
        ];

        return $this->database->Execute($query, $params);
    }

    public function GetBarciedienst(int $dayId, int $userId, int $shift): ?Barciedienst
    {
        $query = 'SELECT 
                    D.date,
                    M.day_id as dayId,
                    U.id as userId,
                    U.name as naam,
                    shift,
                    is_bhv as isBhv
                  FROM barcie_schedule_map M
                  INNER JOIN barcie_days D ON M.day_id = D.id
                  INNER JOIN J3_users U ON M.user_id = U.id
                  WHERE day_id = ? and
                        user_id = ? and
                        shift = ?';
        $params = [
            $dayId,
            $userId,
            $shift
        ];

        $rows = $this->database->Execute($query, $params);
        if (count($rows) != 1) {
            return null;
        }

        return new Barciedienst(
            DateFunctions::CreateDateTime($rows[0]->date),
            new Persoon($rows[0]->userId, $rows[0]->naam),
            $rows[0]->shift,
            $rows[0]->isBhv === 1
        );
    }

    public function GetBarciediensten(): array
    {
        $query = 'SELECT
                    M.userId,
                    M.naam,
                    M.is_bhv as isBhv,
                    D.date,
                    M.shift
                  FROM barcie_days D
                  LEFT JOIN (
                    SELECT
                        M.day_id,
                        M.shift,
                        U.id as userId,                        
                        U.name as naam,
                        M.is_bhv
                    FROM barcie_schedule_map M
                    INNER JOIN J3_users U on U.id = M.user_id
                  ) M on M.day_id = D.id
                  WHERE D.date >= CURRENT_DATE()
                  ORDER BY date, shift, naam';
        $rows = $this->database->Execute($query);
        $result = [];
        foreach ($rows as $row) {
            $persoon = $row->userId ? new Persoon($row->userId, $row->naam) : null;
            $result[] = new Barciedienst(
                DateFunctions::CreateDateTime($row->date),
                $persoon,
                $row->shift,
                $row->isBhv
            );
        }
        return $result;
    }

    public function GetBarciedienstenForDate(DateTime $date): array
    {
        $query = "SELECT
                    D.date,
                    U.id as userId,
                    U.name as naam,
                    U.email,
                    shift,
                    is_bhv as isBhv
                  FROM barcie_schedule_map M
                  INNER JOIN J3_users U ON M.user_id = U.id
                  INNER JOIN barcie_days D ON M.day_id = D.id
                  WHERE D.date = ?";
        $params = [$date->format("Y-m-d")];
        $result = $this->database->Execute($query, $params);

        $diensten = [];
        foreach ($result as $dienst) {
            $diensten[] = new Barciedienst(
                $dienst->date,
                new Persoon(
                    $dienst->userId,
                    $dienst->naam,
                    $dienst->email
                ),
                $dienst->shift,
                $dienst->isBhv
            );
        }

        return $diensten;
    }

    public function GetBarcielidById(int $userId): ?Barcielid
    {
        $query = 'SELECT
                    U.id as userId,
                    U.name AS naam,
                    count(B.id) AS aantalDiensten
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON G.id = M.group_id
                  LEFT JOIN barcie_schedule_map B ON B.user_id = U.id
                  WHERE title = "Barcie" and U.id = ?
                  GROUP BY U.id
                  ORDER BY count(B.id) ASC';
        $params = [$userId];
        $rows = $this->database->Execute($query, $params);

        if (count($rows) != 1) {
            return null;
        }

        return new Barcielid($rows[0]->userId, $rows[0]->naam, $rows[0]->aantalDiensten);
    }

    public function GetBarcieleden(): array
    {
        $query = 'SELECT
                    U.id as userId,
                    U.name AS naam,
                    count(B.id) AS aantalDiensten
                  FROM J3_users U
                  INNER JOIN J3_user_usergroup_map M ON U.id = M.user_id
                  INNER JOIN J3_usergroups G ON G.id = M.group_id
                  LEFT JOIN barcie_schedule_map B ON B.user_id = U.id
                  WHERE title = "Barcie"
                  GROUP BY U.id
                  ORDER BY count(B.id) ASC';
        $rows =  $this->database->Execute($query);
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Barcielid($row->userId, $row->naam, $row->aantalDiensten);
        }
        return $result;
    }

    public function InsertBarciedienst(Barciedienst $dienst, int $dayId)
    {
        $query = 'INSERT INTO barcie_schedule_map (day_id, user_id, shift)
                  VALUES (?, ?, ?)';
        $params = [$dayId, $dienst->persoon->id, $dienst->shift];

        return $this->database->Execute($query, $params);
    }

    public function DeleteBarciedienst(Barciedienst $barciedienst)
    {
        $query = 'DELETE FROM barcie_schedule_map
                  WHERE id = ?';
        $params = [$barciedienst->id];

        $this->database->Execute($query, $params);
    }

    public function DeleteBarcieDay(int $id)
    {
        $query = 'DELETE FROM barcie_days
                  WHERE id = ?';
        $params = [$id];

        $this->database->Execute($query, $params);
    }

    public function ToggleBhv(Barciedienst $barciedienst)
    {
        $query = 'UPDATE barcie_schedule_map
                  SET is_bhv = IF(is_bhv = 1, 0, 1)
                  WHERE id = ?';
        $params = [$barciedienst->id];

        $this->database->Execute($query, $params);
    }

    public function GetBarciedienstenByUserId($userId)
    {
        $query = 'SELECT 
                    U.id as userId, 
                    U.name as naam, 
                    U.email,
                    D.date, 
                    M.shift, 
                    M.is_bhv AS isBhv
                  FROM J3_users U
                  INNER JOIN barcie_schedule_map M ON M.user_id = U.id
                  INNER JOIN barcie_days D ON M.day_id = D.id
                  WHERE U.id = ? AND D.date >= NOW()';
        $params = [$userId];

        $rows = $this->database->Execute($query, $params);
        $diensten = [];
        foreach ($rows as $row) {
            $diensten[] = new Barciedienst(
                DateFunctions::CreateDateTime($row->date),
                new Persoon(
                    $row->userId,
                    $row->naam,
                    $row->email
                ),
                $row->shift,
                $row->isBhv
            );
        }

        return $diensten;
    }
}
