<?php

class BarcieGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetBardagen(): array
    {
        $query = 'SELECT 
                    D.id,
                    date,
                    U.id AS userId,
                    U.name AS naam,
                    U.email
                    shift,
                    is_bhv AS isBhv
                  FROM barcie_days D
                  LEFT JOIN barcie_schedule_map M ON D.id = M.day_id
                  LEFT JOIN J3_users U ON U.id = M.user_id
                  WHERE CURRENT_DATE() <= D.date
                  ORDER BY date, shift, name';
        $rows = $this->database->Execute($query);
        return $this->MapToBardagen($rows);
    }

    public function AddBardag(DateTime $date)
    {
        $query = 'INSERT INTO barcie_days (date) VALUES (?)';
        $params = [DateFunctions::GetYmdNotation($date)];
        $this->database->Execute($query, $params);
    }

    public function GetBeschikbaarheden(Persoon $persoon): array
    {
        $query = 'SELECT 
                    A.id,
                    U.id AS userId,
                    U.name AS naam,
                    D.date, 
                    A.is_beschikbaar AS isBeschikbaar
                  FROM barcie_availability A
                  INNER JOIN J3_users U ON A.user_id = U.id
                  INNER JOIN barcie_days D on A.day_id = D.id
                  WHERE A.user_id = ? and D.date >= CURRENT_DATE()';
        $params = [$persoon->id];
        $rows = $this->database->Execute($query, $params);
        $beschikbaarheden = $this->MapToBeschikbaarheden($rows);
        return count($beschikbaarheden) > 0 ? $beschikbaarheden[0] : null;
    }

    public function GetBeschikbaarhedenForDate(DateTime $date): array
    {
        $query = 'SELECT
                    A.id,
                    U.id AS userId,
                    U.name AS naam,
                    D.date,
                    A.is_beschikbaar AS isBeschikbaar
                  FROM barcie_availability A
                  INNER JOIN J3_users U ON A.user_id = U.id
                  INNER JOIN barcie_days D on A.day_id = D.id
                  WHERE D.date = ?';
        $params = [DateFunctions::GetYmdNotation($date)];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToBeschikbaarheden($rows);
    }

    public function GetBeschikbaarheid(Persoon $user, int $dayId): ?Barciebeschikbaarheid
    {
        $query = 'SELECT
                    A.id,
                    U.id AS userId,
                    U.name AS naam,
                    U.email,
                    D.date, 
                    A.is_beschikbaar AS isBeschikbaar
                  FROM barcie_availability A
                  INNER JOIN barcie_days D ON A.day_id = D.id
                  INNER JOIN J3_users U ON A.user_id = U.id
                  WHERE user_id = ? and day_id = ?';
        $params = [$user->id, $dayId];
        $rows = $this->database->Execute($query, $params);
        $beschikbaarheden = $this->MapToBeschikbaarheden($rows);
        return count($beschikbaarheden) > 0 ? $beschikbaarheden[0] : null;
    }

    public function UpdateBeschikbaarheid(Barciebeschikbaarheid $beschikbaarheid): void
    {
        $query = 'UPDATE barcie_availability
                  SET is_beschikbaar = ?
                  WHERE id = ?';
        $params = [
            $beschikbaarheid->isBeschikbaar ? "Ja" : "Nee",
            $beschikbaarheid->id
        ];
        $this->database->Execute($query, $params);
    }

    public function DeleteBeschikbaarheid(Barciebeschikbaarheid $beschikbaarheid): void
    {
        $query = 'DELETE FROM barcie_availability WHERE id = ?';
        $params = [$beschikbaarheid->id];
        $this->database->Execute($query, $params);
    }

    public function InsertBeschikbaarheid(Barciebeschikbaarheid $beschikbaarheid): void
    {
        $query = 'INSERT INTO barcie_availability (day_id, user_id, is_beschikbaar)
                  VALUES (?, ?, ?)';
        $params = [
            $beschikbaarheid->bardag->id,
            $beschikbaarheid->persoon->id,
            $beschikbaarheid->isBeschikbaar
        ];
        $this->database->Execute($query, $params);
    }

    public function GetBardienst(int $dayId, int $userId, int $shift): ?Bardienst
    {
        $query = 'SELECT 
                    M.id,
                    D.date,
                    M.day_id AS dayId,
                    U.id AS userId,
                    U.name AS naam,
                    shift,
                    is_bhv AS isBhv
                  FROM barcie_schedule_map M
                  INNER JOIN barcie_days D ON M.day_id = D.id
                  INNER JOIN J3_users U ON M.user_id = U.id
                  WHERE day_id = ? and
                        user_id = ? and
                        shift = ?';
        $params = [$dayId, $userId, $shift];
        $rows = $this->database->Execute($query, $params);
        $beschikbaarheden = $this->MapToBeschikbaarheden($rows);
        return count($beschikbaarheden) > 0 ? $beschikbaarheden[0] : null;
    }

    public function GetBardiensten(): array
    {
        $query = 'SELECT
                    M.userId,
                    M.naam,
                    M.is_bhv AS isBhv,
                    D.date,
                    M.shift
                  FROM barcie_days D
                  LEFT JOIN (
                    SELECT
                        M.day_id,
                        M.shift,
                        U.id AS userId,                        
                        U.name AS naam,
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
            $result[] = new Bardienst(
                DateFunctions::CreateDateTime($row->date),
                $persoon,
                $row->shift,
                $row->isBhv
            );
        }
        return $result;
    }

    public function GetBardienstenForDate(DateTime $date): array
    {
        $query = "SELECT
                    D.date,
                    U.id AS userId,
                    U.name AS naam,
                    U.email,
                    shift,
                    is_bhv AS isBhv
                  FROM barcie_schedule_map M
                  INNER JOIN J3_users U ON M.user_id = U.id
                  INNER JOIN barcie_days D ON M.day_id = D.id
                  WHERE D.date = ?";
        $params = [$date->format("Y-m-d")];
        $result = $this->database->Execute($query, $params);

        $diensten = [];
        foreach ($result as $dienst) {
            $diensten[] = new Bardienst(
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

    public function GetBarleden(): array
    {
        $query = 'SELECT
                    U.id AS userId,
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
            $barlid = new Barlid($row->userId, $row->naam);
            $barlid->aantalDiensten = $row->aantalDiensten;
            $result[] = $barlid;
        }
        return $result;
    }

    public function InsertBardienst(Bardienst $dienst, int $dayId)
    {
        $query = 'INSERT INTO barcie_schedule_map (day_id, user_id, shift)
                  VALUES (?, ?, ?)';
        $params = [$dayId, $dienst->persoon->id, $dienst->shift];

        return $this->database->Execute($query, $params);
    }

    public function DeleteBardienst(Bardienst $bardienst)
    {
        $query = 'DELETE FROM barcie_schedule_map
                  WHERE id = ?';
        $params = [$bardienst->id];

        $this->database->Execute($query, $params);
    }

    public function DeleteBarcieDay(int $id)
    {
        $query = 'DELETE FROM barcie_days
                  WHERE id = ?';
        $params = [$id];

        $this->database->Execute($query, $params);
    }

    public function ToggleBhv(Bardienst $bardienst)
    {
        $query = 'UPDATE barcie_schedule_map
                  SET is_bhv = IF(is_bhv = 1, 0, 1)
                  WHERE id = ?';
        $params = [$bardienst->id];

        $this->database->Execute($query, $params);
    }

    public function GetBardienstenForUser(Persoon $user)
    {
        $query = 'SELECT 
                    U.id AS userId, 
                    U.name AS naam, 
                    U.email,
                    D.date, 
                    M.shift, 
                    M.is_bhv AS isBhv
                  FROM J3_users U
                  INNER JOIN barcie_schedule_map M ON M.user_id = U.id
                  INNER JOIN barcie_days D ON M.day_id = D.id
                  WHERE U.id = ? AND D.date >= NOW()';
        $params = [$user->id];

        $rows = $this->database->Execute($query, $params);
        $diensten = [];
        foreach ($rows as $row) {
            $diensten[] = new Bardienst(
                new Bardag(DateFunctions::CreateDateTime($row->date)),
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

    private function MapToBeschikbaarheden(array $rows)
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Barciebeschikbaarheid(
                $row->id,
                new Persoon(
                    $row->userId,
                    $row->naam,
                    $row->email
                ),
                DateFunctions::CreateDateTime($row->date),
                $row->isBeschikbaar == "Ja"
            );
        }
        return $result;
    }

    private function MapToBardagen(array $rows): array
    {
        $result = [];
        $currentDag = null;
        $currentShift = null;
        foreach ($rows as $row) {
            $date = DateFunctions::CreateDateTime($row->date);
            if ($currentDag !== $row->date) {
                $currentDag = $row->date;
                $result[] = new Bardag($date);
                $currentShift = null;
            }
            $i = count($result) - 1;

            if ($row->shift == null) {
                $currentShift = null;
                continue;
            }

            if ($currentShift != $row->shift) {
                $currentShift = $row->shift;
                $result[$i]->shifts[] = new Barshift(
                    $row->shift,
                    $row->id
                );
            }
            $j = count($result[$i]->shifts) - 1;

            $barlid = new Barlid(
                $row->userId,
                $row->naam,
                $row->email
            );
            $barlid->shift = $row->shift;
            $barlid->isBhv = $row->isBhv == 1;
            $result[$i]->shifts[$j]->barleden[] = $barlid;
        }

        return $result;
    }
}
