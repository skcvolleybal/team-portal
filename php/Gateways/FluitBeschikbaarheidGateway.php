<?php

namespace TeamPortal\Gateways;

use TeamPortal\Common\Database;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities;

class FluitBeschikbaarheidGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetFluitBeschikbaarheden(Entities\Persoon $user): array
    {
        $query = 'SELECT 
                    B.id,
                    U.id AS userId,
                    U.name AS naam,
                    U.email,
                    B.date,
                    SUBSTRING(B.`time`, 1, 5) AS time,
                    B.is_beschikbaar AS isBeschikbaar
                  FROM TeamPortal_fluitbeschikbaarheid B
                  INNER JOIN J3_users U ON U.id = B.user_id
                  WHERE user_id = ?';
        $params = [$user->id];

        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Entities\Beschikbaarheid(
                $row->id,
                new Entities\Persoon($row->userId, $row->naam, $row->email),
                DateFunctions::CreateDateTime($row->date, $row->time),
                $row->isBeschikbaar === "Ja"
            );
        }
        return $result;
    }

    public function GetFluitBeschikbaarheid(Entities\Persoon $user, \DateTime $date): Entities\Beschikbaarheid
    {
        $query = 'SELECT 
                    B.id,
                    U.id AS userId,
                    U.name AS naam,
                    U.email,
                    date,
                    time,
                    is_beschikbaar AS isBeschikbaar
                  FROM TeamPortal_fluitbeschikbaarheid B
                  INNER JOIN J3_users U on B.user_id = U.id
                  WHERE user_id = ? and date = ? and time = ?';
        $params = [
            $user->id,
            DateFunctions::GetYmdNotation($date),
            DateFunctions::GetTime($date)
        ];

        $rows = $this->database->Execute($query, $params);
        if (count($rows) != 1) {
            return new Entities\Beschikbaarheid(null, $user, $date, null);
        }
        return new Entities\Beschikbaarheid(
            $rows[0]->id,
            new Entities\Persoon($rows[0]->id, $rows[0]->naam, $rows[0]->email),
            DateFunctions::CreateDateTime($rows[0]->date),
            $rows[0]->isBeschikbaar === "Ja"
        );
    }

    public function GetAllBeschikbaarheden(\DateTime $date): array
    {
        $query = 'SELECT         
                    F.id,
                    U.id AS userId,
                    U.name AS naam,
                    U.email,
                    date,
                    time,
                    is_beschikbaar AS isBeschikbaar
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
            $persoon = new Entities\Persoon($row->userId, $row->naam, $row->email);
            $result[] = new Entities\Beschikbaarheid(
                $row->id,
                $persoon,
                \DateTime::createFromFormat('Y-m-d H:i:s', $row->date . ' ' . $row->time),
                $row->isBeschikbaar === "Ja"
            );
        }
        return $result;
    }

    public function Insert(Entities\Beschikbaarheid $beschikbaarheid): void
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

    public function Update(Entities\Beschikbaarheid $beschikbaarheid): void
    {
        $query = 'UPDATE TeamPortal_fluitbeschikbaarheid
                  SET is_beschikbaar = ?
                  WHERE id = ?';

        $params = [
            $beschikbaarheid->isBeschikbaar,
            $beschikbaarheid->id
        ];

        $this->database->Execute($query, $params);
    }

    public function Delete(Entities\Beschikbaarheid $beschikbaarheid): void
    {
        $query = 'DELETE FROM TeamPortal_fluitbeschikbaarheid
                  WHERE id = ?';
        $params = [$beschikbaarheid->id];

        $this->database->Execute($query, $params);
    }
}
