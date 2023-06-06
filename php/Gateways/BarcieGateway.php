<?php

namespace TeamPortal\Gateways;

use DateTime;
use TeamPortal\Common\Database;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Barbeschikbaarheid;
use TeamPortal\Entities\Bardag;
use TeamPortal\Entities\Bardienst;
use TeamPortal\Entities\Barlid;
use TeamPortal\Entities\Barshift;
use TeamPortal\Entities\Persoon;
use TeamPortal\UseCases\IBarcieGateway;

class BarcieGateway implements IBarcieGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetBardagen(): array
    {
        // WP ready 

        $query = 'SELECT 
            D.id,
            date,
            U.id AS userId,
            U.display_name AS naam,
            U.user_email AS email,
            shift,
            is_bhv AS isBhv
            FROM ' . $_ENV['DBNAME'] . '.barcie_days D
            LEFT JOIN ' . $_ENV['DBNAME'] . '.barcie_schedule_map M ON D.id = M.day_id
            LEFT JOIN ' . $_ENV['WPDBNAME'] . '.wp_users U ON U.id = M.user_id
            WHERE CURRENT_DATE() <= D.date
            ORDER BY date, shift, naam';

                
        $rows = $this->database->Execute($query);
        return $this->MapToBardagen($rows);
    }

    public function GetBardag(DateTime $date): Bardag
    {

        $query = 'SELECT 
                D.id,
                date,
                U.id AS userId,
                U.display_name AS naam,
                U.user_email AS email,
                shift,
                is_bhv AS isBhv
            FROM ' . $_ENV['DBNAME'] . '.barcie_days D
            LEFT JOIN ' . $_ENV['DBNAME'] . '.barcie_schedule_map M ON D.id = M.day_id
            LEFT JOIN ' . $_ENV['WPDBNAME'] . '.wp_users U ON U.id = M.user_id
            WHERE D.date = ?';
 
        // $query = 'SELECT 
        //             D.id,
        //             date,
        //             U.id AS userId,
        //             U.name AS naam,
        //             U.email,
        //             shift,
        //             is_bhv AS isBhv
        //           FROM barcie_days D
        //           LEFT JOIN barcie_schedule_map M ON D.id = M.day_id
        //           LEFT JOIN J3_users U ON U.id = M.user_id
        //           WHERE D.date = ?';
        $params = [DateFunctions::GetYmdNotation($date)];
        $rows = $this->database->Execute($query, $params);
        return count($rows) > 0 ? $this->MapToBardagen($rows)[0] : new Bardag(null, $date);
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
                    U.email,
                    D.id as dagId,
                    D.date,
                    A.is_beschikbaar AS isBeschikbaar
                  FROM barcie_availability A
                  INNER JOIN J3_users U ON A.user_id = U.id
                  INNER JOIN barcie_days D on A.day_id = D.id
                  WHERE A.user_id = ? and D.date >= CURRENT_DATE()';
        $params = [$persoon->id];
        $rows = $this->database->Execute($query, $params);
        $beschikbaarheden = $this->MapToBeschikbaarheden($rows);
        return count($beschikbaarheden) > 0 ? $beschikbaarheden : [];
    }

    public function GetBeschikbaarhedenForDate(DateTime $date): array
    {
        $query = 'SELECT
                    A.id,
                    U.id AS userId,
                    U.name AS naam,
                    U.email,
                    D.id as dagId,
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

    public function GetBeschikbaarheid(Persoon $user, Bardag $bardag): Barbeschikbaarheid
    {
        $query = 'SELECT
                    A.id,
                    U.id AS userId,
                    U.name AS naam,
                    U.email,
                    D.id as dagId,
                    D.date, 
                    A.is_beschikbaar AS isBeschikbaar
                  FROM barcie_availability A
                  INNER JOIN barcie_days D ON A.day_id = D.id
                  INNER JOIN J3_users U ON A.user_id = U.id
                  WHERE user_id = ? and day_id = ?';
        $params = [$user->id, $bardag->id];
        $rows = $this->database->Execute($query, $params);
        $beschikbaarheden = $this->MapToBeschikbaarheden($rows);
        return count($beschikbaarheden) > 0 ?
            $beschikbaarheden[0] :
            new Barbeschikbaarheid($bardag, $user, null);
    }

    public function UpdateBeschikbaarheid(Barbeschikbaarheid $beschikbaarheid): void
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

    public function DeleteBeschikbaarheid(Barbeschikbaarheid $beschikbaarheid): void
    {
        $query = 'DELETE FROM barcie_availability WHERE id = ?';
        $params = [$beschikbaarheid->id];
        $this->database->Execute($query, $params);
    }

    public function InsertBeschikbaarheid(Barbeschikbaarheid $beschikbaarheid): void
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

    public function GetBardienst(Bardag $dag, Persoon $user, int $shift): Bardienst
    {
        $query = 'SELECT 
                    M.id,
                    D.id as dayId,
                    D.date,
                    M.day_id AS dayId,
                    U.id AS userId,
                    U.name AS naam,
                    U.email,
                    shift,
                    is_bhv AS isBhv
                  FROM barcie_schedule_map M
                  INNER JOIN barcie_days D ON M.day_id = D.id
                  INNER JOIN J3_users U ON M.user_id = U.id
                  WHERE day_id = ? and
                        user_id = ? and
                        shift = ?';
        $params = [$dag->id, $user->id, $shift];
        $rows = $this->database->Execute($query, $params);

        return count($rows) > 0 ?
            new Bardienst(
                new Bardag($rows[0]->dayId, DateFunctions::CreateDateTime($rows[0]->date)),
                new Persoon($rows[0]->userId, $rows[0]->naam, $rows[0]->email),
                $rows[0]->shift,
                $rows[0]->isBhv,
                $rows[0]->id
            ) :
            new Bardienst($dag, $user, $shift, false);
    }

    public function GetBardiensten(): array
    {
        $query = 'SELECT
                    M.userId,
                    M.naam,
                    M.email,
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
                        U.email,
                        M.is_bhv
                    FROM barcie_schedule_map M
                    INNER JOIN J3_users U on U.id = M.user_id
                  ) M on M.day_id = D.id
                  WHERE D.date >= CURRENT_DATE()
                  ORDER BY date, shift, naam';
        $rows = $this->database->Execute($query);
        $result = [];
        foreach ($rows as $row) {
            $persoon = $row->userId ? new Persoon($row->userId, $row->naam, $row->email) : null;
            $result[] = new Bardienst(
                new Bardag($row->id, DateFunctions::CreateDateTime($row->date)),
                $persoon,
                $row->shift,
                $row->isBhv
            );
        }
        return $result;
    }

    public function GetBarleden(): array
    {
        // WP Not yet Ready: should be tested extensively
        // Seems ready

        // Maps WordPress user ID's on previous barcie_schedule_map Joomla User ID's
        // ID's are not the same, so either WP users should get Joomla's old ID's, or all barcie_schedule_map user ID's should be wiped. that should happen each season. 

        $args = array(
            'role'    => 'Barcie');
        $users = get_users( $args );

        // Map the aantal bardiensten on WordPress user ids
        $query = 'SELECT B.user_id AS userId, 
                count(B.id) AS aantalDiensten
                FROM barcie_schedule_map B
                GROUP BY B.user_id
                ORDER BY count(B.id) ASC';

        $rows =  $this->database->Execute($query);

        
        $result = [];
        foreach ($users as $user) {
            $user->aantalDiensten = 0;
            foreach ($rows as $bardienst) {
                if ($user->ID == $bardienst->userId) {
                    $user->aantalDiensten = $bardienst->aantalDiensten;
                }
            }
        }

        foreach ($users as $user) {
            $barlid = new Barlid(
                new Persoon($user->data->ID, $user->data->user_nicename, $user->data->user_email),
                $user->aantalDiensten
            );
            $result[] = $barlid;

        }
        return $result;
    }

    public function InsertBardienst(Bardienst $dienst): void
    {
        $query = 'INSERT INTO barcie_schedule_map (day_id, user_id, shift)
                  VALUES (?, ?, ?)';
        $params = [$dienst->bardag->id, $dienst->persoon->id, $dienst->shift];

        $this->database->Execute($query, $params);
    }

    public function DeleteBardienst(Bardienst $bardienst): void
    {
        $query = 'DELETE FROM barcie_schedule_map
                  WHERE id = ?';
        $params = [$bardienst->id];

        $this->database->Execute($query, $params);
    }

    public function DeleteBardag(Bardag $bardag): void
    {
        $query = 'DELETE FROM barcie_days
                  WHERE id = ?';
        $params = [$bardag->id];

        $this->database->Execute($query, $params);
    }

    public function ToggleBhv(Bardienst $bardienst): void
    {
        $query = 'UPDATE barcie_schedule_map
                  SET is_bhv = IF(is_bhv = 1, 0, 1)
                  WHERE id = ?';
        $params = [$bardienst->id];

        $this->database->Execute($query, $params);
    }

    public function GetBardienstenForUser(Persoon $user): array
    {
        // WP Working
        $query = 'SELECT 
                U.id AS userId, 
                U.display_name AS naam, 
                U.user_email AS email,
                D.id as dayId,
                D.date, 
                M.shift, 
                M.is_bhv AS isBhv
            FROM ' . $_ENV['WPDBNAME'] . '.wp_users U
            INNER JOIN ' . $_ENV['DBNAME'] . '.barcie_schedule_map M ON M.user_id = U.id
            INNER JOIN ' . $_ENV['DBNAME'] . '.barcie_days D ON M.day_id = D.id
            WHERE U.id = ? AND D.date >= CURDATE()';

        $params = [$user->id];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToBardiensten($rows);
    }

    public function MapToBardiensten(array $rows): array
    {
        // WP working
        $diensten = [];
        foreach ($rows as $row) {
            $diensten[] = new Bardienst(
                new Bardag($row->dayId, DateFunctions::CreateDateTime($row->date)),
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

    public function MapToBeschikbaarheden(array $rows): array
    {
        $result = [];
        foreach ($rows as $row) {
            $result[] = new Barbeschikbaarheid(
                new Bardag($row->dagId, DateFunctions::CreateDateTime($row->date)),
                new Persoon(
                    $row->userId,
                    $row->naam,
                    $row->email
                ),
                $row->isBeschikbaar == "Ja",
                $row->id
            );
        }
        return $result;
    }

    public function MapToBardagen(array $rows): array
    {
        $result = [];
        $currentDag = null;
        $currentShift = null;
        foreach ($rows as $row) {
            if ($currentDag !== $row->date) {
                $currentDag = $row->date;
                $result[] = new Bardag($row->id, DateFunctions::CreateDateTime($row->date));
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
                new Persoon(
                    $row->userId,
                    $row->naam,
                    $row->email
                )
            );
            $barlid->shift = $row->shift;
            $barlid->isBhv = $row->isBhv == 1;
            $result[$i]->shifts[$j]->barleden[] = $barlid;
        }

        return $result;
    }
}
