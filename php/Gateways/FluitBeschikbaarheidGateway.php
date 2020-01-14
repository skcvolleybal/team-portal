<?php

class FluitBeschikbaarheidGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetFluitBeschikbaarheden(int $userId): array
    {
        $query = 'SELECT 
                    B.id,
                    U.id as userId,
                    U.name as naam,
                    B.date,
                    SUBSTRING(B.`time`, 1, 5) as time,
                    B.is_beschikbaar as isBeschikbaar
                  FROM TeamPortal_fluitbeschikbaarheid B
                  INNER JOIN J3_users U ON U.id = B.user_id
                  WHERE user_id = ?';
        $params = [$userId];

        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Beschikbaarheid(
                $row->id,
                new Persoon($row->userId, $row->naam),
                DateFunctions::CreateDateTime($row->date, $row->time),
                $row->isBeschikbaar === "Ja"
            );
        }
        return $result;
    }

    public function GetFluitBeschikbaarheid(int $userId, DateTime $date): ?Beschikbaarheid
    {
        $query = 'SELECT 
                    B.id,
                    U.id as userId,
                    U.name as naam,
                    date,
                    time,
                    is_beschikbaar as isBeschikbaar
                  FROM TeamPortal_fluitbeschikbaarheid B
                  INNER JOIN J3_users U on B.user_id = U.id
                  WHERE user_id = ? and date = ? and time = ?';
        $params = [
            $userId,
            DateFunctions::GetYmdNotation($date),
            DateFunctions::GetTime($date)
        ];

        $rows = $this->database->Execute($query, $params);
        if (count($rows) != 1) {
            return null;
        }
        return new Beschikbaarheid(
            $rows[0]->id,
            new Persoon($rows[0]->id, $rows[0]->naam),
            DateFunctions::CreateDateTime($rows[0]->date),
            $rows[0]->isBeschikbaar === "Ja"
        );
    }

    public function GetAllBeschikbaarheden(DateTIme $date): array
    {
        $query = 'SELECT         
                    F.id,
                    U.id as userId,
                    U.name as naam,
                    date,
                    time,
                    is_beschikbaar as isBeschikbaar
                  FROM TeamPortal_fluitbeschikbaarheid F
                  INNER JOIN J3_users U on F.user_id = U.id
                  WHERE date = ? and time = ?';
        $params = [
            DateFunctions::GetYmdNotation($date),
            DateFunctions::GetTime($date)
        ];

        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $persoon = new Persoon($row->userId, $row->naam);
            $result[] = new Beschikbaarheid(
                $row->id,
                $persoon,
                DateTime::createFromFormat('Y-m-d H:i:s', $row->date . ' ' . $row->time),
                $row->isBeschikbaar === "Ja"
            );
        }
        return $result;
    }

    public function Insert(Beschikbaarheid $beschikbaarheid)
    {
        $query = 'INSERT INTO TeamPortal_fluitbeschikbaarheid (user_id, date, time, is_beschikbaar) 
                  VALUES (?, ?, ?, ?)';
        $params = [
            $beschikbaarheid->persoon->id,
            DateFunctions::GetYmdNotation($beschikbaarheid->date),
            DateFunctions::GetTime($beschikbaarheid->date),
            $beschikbaarheid->isBeschikbaar ? "Ja" : "Nee",
        ];

        $this->database->Execute($query, $params);
    }

    public function Update(Beschikbaarheid $beschikbaar)
    {
        $query = 'UPDATE TeamPortal_fluitbeschikbaarheid
                  SET is_beschikbaar = ?
                  WHERE id = ?';

        $params = [$beschikbaar->isBeschikbaar, $beschikbaar->id];

        $this->database->Execute($query, $params);
    }

    public function Delete(Beschikbaarheid $beschikbaar)
    {
        $query = 'DELETE FROM TeamPortal_fluitbeschikbaarheid
                  WHERE id = ?';
        $params = [$beschikbaar->id];

        $this->database->Execute($query, $params);
    }
}
