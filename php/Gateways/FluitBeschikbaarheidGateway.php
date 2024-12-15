<?php

namespace TeamPortal\Gateways;

use DateTime;
use TeamPortal\Common\Database;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Beschikbaarheid;
use TeamPortal\Entities\Persoon;

class BeschikbaarheidGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetBeschikbaarheden(Persoon $user): array
    {

        // Working in WP
        $query = 'SELECT 
                    B.id,
                    U.id AS userId,
                    U.display_name AS naam,
                    U.user_email as email,
                    B.date,
                    SUBSTRING(B.`time`, 1, 5) AS time,
                    B.is_beschikbaar AS isBeschikbaar
                  FROM TeamPortal_fluitbeschikbaarheid B
                  INNER JOIN wp_users U ON U.id = B.user_id
                  WHERE user_id = ?';

        // Old Joomla query
        // $query = 'SELECT 
        //             B.id,
        //             U.id AS userId,
        //             U.name AS naam,
        //             U.email,
        //             B.date,
        //             SUBSTRING(B.`time`, 1, 5) AS time,
        //             B.is_beschikbaar AS isBeschikbaar
        //           FROM TeamPortal_fluitbeschikbaarheid B
        //           INNER JOIN J3_users U ON U.id = B.user_id
        //           WHERE user_id = ?';
        $params = [$user->id];

        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Beschikbaarheid(
                $row->id,
                new Persoon($row->userId, $row->naam, $row->email),
                DateFunctions::CreateDateTime($row->date, $row->time),
                $row->isBeschikbaar === "Ja"
            );
        }
        return $result;
    }

    public function GetBeschikbaarheid(Persoon $user, DateTime $date): Beschikbaarheid
    {
        // Todo: test edited query 
        $query = 'SELECT 
                    B.id,
                    U.id AS userId,
                    U.display_name AS naam,
                    U.user_email as email,
                    date,
                    time,
                    is_beschikbaar AS isBeschikbaar
                  FROM TeamPortal_fluitbeschikbaarheid B
                  INNER JOIN wp_users U on B.user_id = U.id
                  WHERE user_id = ? and date = ? and time = ?';
        $params = [
            $user->id,
            DateFunctions::GetYmdNotation($date),
            DateFunctions::GetTime($date)
        ];

        $rows = $this->database->Execute($query, $params);
        if (count($rows) != 1) {
            return new Beschikbaarheid(null, $user, $date, null);
        }
        return new Beschikbaarheid(
            $rows[0]->id,
            new Persoon($rows[0]->id, $rows[0]->naam, $rows[0]->email),
            DateFunctions::CreateDateTime($rows[0]->date),
            $rows[0]->isBeschikbaar === "Ja"
        );
    }

    public function GetAllBeschikbaarheden(DateTime $date): array
    {
        $query = 'SELECT         
                    F.id,
                    U.id AS userId,
                    U.display_name AS naam,
                    U.user_email as email,
                    date,
                    time,
                    is_beschikbaar AS isBeschikbaar
                  FROM TeamPortal_fluitbeschikbaarheid F
                  INNER JOIN wp_users U on F.user_id = U.id
                  WHERE date = ? and time = ?';
        $params = [
            DateFunctions::GetYmdNotation($date),
            DateFunctions::GetTime($date)
        ];

        $rows = $this->database->Execute($query, $params);
        $result = [];
        foreach ($rows as $row) {
            $persoon = new Persoon($row->userId, $row->naam, $row->email);
            $result[] = new Beschikbaarheid(
                $row->id,
                $persoon,
                DateTime::createFromFormat('Y-m-d H:i:s', $row->date . ' ' . $row->time),
                $row->isBeschikbaar === "Ja"
            );
        }
        return $result;
    }

    public function Insert(Beschikbaarheid $beschikbaarheid): void
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

    public function Update(Beschikbaarheid $beschikbaarheid): void
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

    public function Delete(Beschikbaarheid $beschikbaarheid): void
    {
        $query = 'DELETE FROM TeamPortal_fluitbeschikbaarheid
                  WHERE id = ?';
        $params = [$beschikbaarheid->id];

        $this->database->Execute($query, $params);
    }

    private function FormatShifts(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'shift_count' => $row->shift_count,
                'display_name' => $row->display_name
            ];
        }
        return $result;
    }

    public function GetScheidsrechterShifts(): array
    {
        $query = '
            WITH scheidsrechter_shifts AS (
                SELECT scheidsrechter_id AS id, COUNT(match_id) AS shift_count
                FROM TeamPortal_wedstrijden
                WHERE timestamp < NOW()
                GROUP BY scheidsrechter_id
            )
            SELECT ss.shift_count, wu.display_name
            FROM scheidsrechter_shifts ss
            JOIN wp_users wu
            ON ss.id = wu.id
            ORDER BY shift_count DESC
            LIMIT 10
        ';

        $rows = $this->database->Execute($query);
        return $this->FormatShifts($rows);
    }

    public function GetTellerShifts(): array
    {
        $query = '
            WITH teller_shifts AS (
                SELECT teller1_id AS id, COUNT(match_id) AS shift_count
                FROM TeamPortal_wedstrijden
                WHERE timestamp < NOW()
                GROUP BY teller1_id
                UNION ALL
                SELECT teller2_id AS id, COUNT(match_id) AS shift_count
                FROM TeamPortal_wedstrijden
                WHERE timestamp < NOW()
                GROUP BY teller2_id
            ),
            total_teller_shifts AS (
                SELECT id, SUM(shift_count) AS total_shift_count
                FROM teller_shifts
                GROUP BY id
            )
            SELECT ts.total_shift_count AS shift_count, wu.display_name
            FROM total_teller_shifts ts
            JOIN wp_users wu
            ON ts.id = wu.id
            ORDER BY shift_count DESC
            LIMIT 10
        ';

        $rows = $this->database->Execute($query);
        return $this->FormatShifts($rows);
    }

    public function GetBHVShifts(): array
    {
        $query = '
            WITH bar_shifts AS (
                SELECT bs.user_id AS id, COUNT(bs.id) AS shift_count
                FROM barcie_schedule_map bs
                JOIN barcie_days bd
                ON bs.day_id = bd.id
                WHERE bd.date < NOW() AND bs.is_bhv
                GROUP BY user_id
            )
            SELECT bs.shift_count, wu.display_name
            FROM bar_shifts bs
            JOIN wp_users wu
            ON bs.id = wu.id
            ORDER BY shift_count DESC
            LIMIT 10
        ';

        $rows = $this->database->Execute($query);
        return $this->FormatShifts($rows);
    }

    public function GetBarPersonnelShifts(): array
    {
        $query = '
            WITH bar_shifts AS (
                SELECT bs.user_id AS id, COUNT(bs.id) AS shift_count
                FROM barcie_schedule_map bs
                JOIN barcie_days bd
                ON bs.day_id = bd.id
                WHERE bd.date < NOW() AND bs.is_bhv IS NULL
                GROUP BY user_id
            )
            SELECT bs.shift_count, wu.display_name
            FROM bar_shifts bs
            JOIN wp_users wu
            ON bs.id = wu.id
            ORDER BY shift_count DESC
            LIMIT 10
        ';

        $rows = $this->database->Execute($query);
        return $this->FormatShifts($rows);
    }
}
