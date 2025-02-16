<?php

namespace TeamPortal\Gateways;

use TeamPortal\Common\Database;
use TeamPortal\Common\DateFunctions;

class SwapGateway
{
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    public function GetAllSwaps(): array
    {
        $query = "SELECT 
                    W.taskToSwapId,
                    W.swapForTaskId,
                    W.userWhoProposedId,
                    W.otherUserId,
                    W.date,
                    W.shift
                  FROM
                    " . $_ENV['DBNAME'] . ".teamtaken_swaps W";
        $rows = $this->database->Execute($query);
        return $this->MapToSwaps($rows);
    }

    public function GetProposedSwaps(): array
    {
        $query = "SELECT
                W.id,
                W.taskToSwapId,
                W.swapForTaskId,
                W.userWhoProposedId,
                W.otherUserId,
                BD1.date AS swapForDate,      -- Date for taskToSwapId
                B1.shift AS swapForShift,     -- Shift for taskToSwapId
                BD2.date AS dateToSwap,     -- Date for swapForTaskId
                B2.shift AS shiftToSwap     -- Shift for swapForTaskId
            FROM " . $_ENV['DBNAME'] . ".teamtaken_swaps W
            INNER JOIN " . $_ENV['DBNAME'] . ".barcie_schedule_map B1 ON W.taskToSwapId = B1.id
            JOIN " . $_ENV['DBNAME'] . ".barcie_days BD1 ON B1.day_id = BD1.id
            JOIN " . $_ENV['DBNAME'] . ".barcie_schedule_map B2 ON W.swapForTaskId = B2.id
            JOIN " . $_ENV['DBNAME'] . ".barcie_days BD2 ON B2.day_id = BD2.id";

        $rows = $this->database->Execute($query);
        return $this->MapToSwaps($rows);
    }

    public function GetSwapsById($data) {
        $query = "SELECT 
                    W.scheduleid,
                    W.userid
                  FROM
                    " . $_ENV['DBNAME'] . ".teamtaken_swaps W
                  WHERE W.userid = (?)
                    ";
        $params = [
            $data->id,
        ];
        $rows = $this->database->Execute($query, $params);
        return $this->MapToSwaps($rows);
    }

    public function UpdateSwaps(object $data)
    {

        $query = 'INSERT INTO teamtaken_swaps (taskToSwapId, swapForTaskId, userWhoProposedId, otherUserId, date, shift)
                  VALUES (?, ?, ?, ?, ?, ?)';
        $params = [
            $data->taskToSwapId,
            $data->swapForTaskId,
            $data->userWhoProposedId,
            $data->otherUserId,
            $data->date,
            $data->shift
        ];
        $this->database->Execute($query, $params);
    }

    public function RemoveSwaps(object $data) {
        $query = 'DELETE FROM ' . $_ENV['DBNAME'] . '.teamtaken_swaps
                  WHERE scheduleid = ?';
        $params = [$data->scheduleid];

        $this->database->Execute($query, $params);
    }

    public function DeleteSwaps(object $data) {
        $query = 'DELETE FROM ' . $_ENV['DBNAME'] . '.teamtaken_swaps
                  WHERE id = ?';
        $params = [$data->id];

        $this->database->Execute($query, $params);
    }

    public function AcceptSwaps(object $data) {
        $query = "UPDATE " . $_ENV['DBNAME'] . ".barcie_schedule_map
                    SET user_id = ?
                    WHERE id = ?";
        $params = [$data->otherUserId, $data->taskToSwapId];
        $this->database->Execute($query, $params);

        $query = "UPDATE " . $_ENV['DBNAME'] . ".barcie_schedule_map
                    SET user_id = ?
                    WHERE id = ?";

        $params = [$data->userWhoProposedId, $data->swapForTaskId];
        $this->database->Execute($query, $params);
    }

    private function MapToSwaps($rows) {
        return $rows;
    }
}
