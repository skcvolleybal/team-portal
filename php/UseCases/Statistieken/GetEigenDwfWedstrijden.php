<?php

namespace TeamPortal\UseCases;

use DwfWedstrijdModel;
use TeamPortal\Gateways\GespeeldeWedstrijdenGateway;
use TeamPortal\Gateways\JoomlaGateway;
use TeamPortal\UseCases\Interactor;

class GetEigenDwfWedstrijden implements Interactor
{
    public function __construct(
        JoomlaGateway $joomlaGateway,
        GespeeldeWedstrijdenGateway $gespeeldeWedstrijdenGateway
    ) {
        $this->gespeeldeWedstrijdenGateway = $gespeeldeWedstrijdenGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    function Execute(object $data = null)
    {
        $user = $this->joomlaGateway->GetUser();
        $team = $user->team;
        if ($team === null) {
            if (count($user->coachteams) !== 1) {
                return null;
            }

            $team = $user->coachteams[0];
        }

        $wedstrijden = $this->gespeeldeWedstrijdenGateway->GetGespeeldeWedstrijdenByTeam($team);

        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            $result[] = new DwfWedstrijdModel($wedstrijd);
        }

        return $result;
    }
}
