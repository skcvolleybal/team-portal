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
        if ($user->team == null) {
            return null;
        }

        $wedstrijden = $this->gespeeldeWedstrijdenGateway->GetGespeeldeWedstrijdenByTeam($user->team);

        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            $result[] = new DwfWedstrijdModel($wedstrijd);
        }

        return $result;
    }
}
