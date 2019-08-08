<?php
include_once 'IInteractor.php';
include_once 'StatistiekenGateway.php';
include_once 'JoomlaGateway.php';

class GetGespeeldePunten implements IInteractor
{

    public function __construct($database)
    {
        $this->statistiekenGateway = new StatistiekenGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        $team = $this->joomlaGateway->GetTeam($userId);
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
