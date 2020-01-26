<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;

class GetGespeeldePunten implements Interactor
{

    public function __construct(
        Gateways\StatistiekenGateway $statistiekenGateway,
        Gateways\JoomlaGateway $joomlaGateway)
    {
        $this->statistiekenGateway = $statistiekenGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $user = $this->joomlaGateway->GetUser();
        $team = $this->joomlaGateway->GetTeam($user);
        if (!$team) {
            throw new \UnexpectedValueException("Je zit niet in een team");
        }
        $spelers = $this->statistiekenGateway->GetGespeeldePunten($team);
        $result = [];
        foreach ($spelers as $speler) {
            if ($speler->naam) {
                $result[] = (object) [
                    'naam' => $speler->GetAfkorting(),
                    "aantalGespeeldePunten" => $speler->aantalGespeeldePunten,
                ];
            }
        }

        return $result;
    }
}
