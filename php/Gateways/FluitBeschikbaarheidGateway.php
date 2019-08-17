<?php

class FluitBeschikbaarheidGateway
{
    public function __construct($database)
    {
        $this->database = $database;
    }

    public function GetFluitBeschikbaarheden($userId)
    {
        $query = 'SELECT *
                  FROM TeamPortal_fluitbeschikbaarheid
                  WHERE user_id = :userId';
        $params = [new Param(Column::UserId, $userId, PDO::PARAM_INT)];

        return $this->database->Execute($query, $params);
    }

    public function GetFluitBeschikbaarheid($userId, $date, $time)
    {
        $query = 'SELECT *
                  FROM TeamPortal_fluitbeschikbaarheid
                  WHERE user_id = :userId and date = :date and time = :time';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::Date, $date, PDO::PARAM_STR),
            new Param(Column::Time, $time, PDO::PARAM_STR),
        ];

        $result = $this->database->Execute($query, $params);
        if (count($result) > 0) {
            return $result[0];
        }
        return null;
    }

    public function GetAllBeschikbaarheid($date, $time)
    {
        $query = 'SELECT *
                  FROM TeamPortal_fluitbeschikbaarheid
                  WHERE date = :date and time = :time';
        $params = [
            new Param(Column::Date, $date, PDO::PARAM_STR),
            new Param(Column::Time, $time, PDO::PARAM_STR),
        ];

        return $this->database->Execute($query, $params);
    }

    public function Insert($userId, $date, $time, $isBeschikbaar)
    {
        $query = 'INSERT INTO TeamPortal_fluitbeschikbaarheid (user_id, date, time, is_beschikbaar) 
                  VALUES (:userId, :date, :time, :isBeschikbaar)';
        $params = [
            new Param(Column::UserId, $userId, PDO::PARAM_INT),
            new Param(Column::Date, $date, PDO::PARAM_STR),
            new Param(Column::Time, $time, PDO::PARAM_STR),
            new Param(Column::IsBeschikbaar, $isBeschikbaar, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    public function Update($id, $isBeschikbaar)
    {
        $query = 'UPDATE TeamPortal_fluitbeschikbaarheid
                  SET is_beschikbaar = :isBeschikbaar
                  WHERE id = :id';

        $params = [
            new Param(':id', $id, PDO::PARAM_INT),
            new Param(Column::IsBeschikbaar, $isBeschikbaar, PDO::PARAM_STR),
        ];

        $this->database->Execute($query, $params);
    }

    public function Delete($id)
    {
        $query = 'DELETE FROM TeamPortal_fluitbeschikbaarheid
                  WHERE id = :id';

        $params = [new Param(':id', $id, PDO::PARAM_INT)];

        $this->database->Execute($query, $params);
    }
}
