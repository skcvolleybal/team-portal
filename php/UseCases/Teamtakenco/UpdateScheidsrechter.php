<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Gateways;
use TeamPortal\Entities\Wedstrijd;

class UpdateScheidsrechter implements Interactor
{
    public function __construct(
        Gateways\TelFluitGateway $telFluitGateway,
        Gateways\WordPressGateway $wordPressGateway,
        Gateways\NevoboGateway $nevoboGateway
    ) {
        $this->telFluitGateway = $telFluitGateway;
        $this->wordPressGateway = $wordPressGateway;
        $this->nevoboGateway = $nevoboGateway;
    }

    public function Execute(object $data = null)
    {
        if ($data->matchId === null) {
            throw new InvalidArgumentException("matchId is null");
        }

        $wedstrijd = $this->telFluitGateway->GetWedstrijd($data->matchId);
        $wedstrijd->scheidsrechter = $this->wordPressGateway->GetScheidsrechter($data->scheidsrechterId);

        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
        $uscWedstrijd = Wedstrijd::GetWedstrijdWithMatchId($uscWedstrijden, $data->matchId);
        $wedstrijd->AppendInformation($uscWedstrijd);

        if ($wedstrijd->id === null) {
            $this->telFluitGateway->Insert($wedstrijd);
        } else {
            if ($wedstrijd->scheidsrechter === null && $wedstrijd->tellers[0] === null && $wedstrijd->tellers[1] === null) {
                $this->telFluitGateway->Delete($wedstrijd);
            } else {
                $this->telFluitGateway->Update($wedstrijd);
            }
        }
    }
}
