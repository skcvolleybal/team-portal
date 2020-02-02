<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways\GespeeldeWedstrijdenGateway;
use TeamPortal\Gateways\JoomlaGateway;

class GetGespeeldePunten implements Interactor
{

    public function __construct(
        GespeeldeWedstrijdenGateway $gespeeldeWedstrijdenGateway,
        JoomlaGateway $joomlaGateway
    ) {
        $this->gespeeldeWedstrijdenGateway = $gespeeldeWedstrijdenGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $user = $this->joomlaGateway->GetUser();
        $team = $this->joomlaGateway->GetTeam($user);
        if (!$team) {
            throw new \UnexpectedValueException("Je zit niet in een team");
        }
        $spelers = $this->gespeeldeWedstrijdenGateway->GetGespeeldePunten($team);
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
