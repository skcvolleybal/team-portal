<?php

class GetGespeeldePunten implements Interactor
{

    public function __construct($database)
    {
        $this->statistiekenGateway = new StatistiekenGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute()
    {
        $user = $this->joomlaGateway->GetUser();
        $team = $this->joomlaGateway->GetTeam($user);
        if (!$team) {
            throw new UnexpectedValueException("Je zit niet in een team");
        }
        $gespeeldePunten = $this->statistiekenGateway->GetGespeeldePunten($team);
        $result = [];
        foreach ($gespeeldePunten as $row) {
            if ($row->naam) {
                $result[] = (object) [
                    'naam' => implode("", array_map(function ($item) {return $item[0];}, explode(" ", $row->naam))),
                    "gespeeldePunten" => $row->gespeeldePunten,
                ];
            }
        }

        return $result;
    }
}
