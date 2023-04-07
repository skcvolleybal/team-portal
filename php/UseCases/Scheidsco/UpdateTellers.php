<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Gateways;
use TeamPortal\Entities\Wedstrijd;

class UpdateTellers implements Interactor
{
    public function __construct(
        Gateways\TelFluitGateway $telFluitGateway,
        Gateways\JoomlaGateway $joomlaGateway,
        Gateways\NevoboGateway $nevoboGateway
    ) {
        $this->telFluitGateway = $telFluitGateway;
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
    }

    public function Execute(object $data = null)
    {
        if ($data->matchId === null) {
            throw new InvalidArgumentException("matchId is null");
        }
        if (!in_array($data->tellerIndex, [0, 1])) {
            throw new InvalidArgumentException("tellerIndex klopt niet: '$data->tellerIndex'");
        }

        $wedstrijd = $this->telFluitGateway->GetWedstrijd($data->matchId);
        $teller = $data->tellerId ? $this->joomlaGateway->GetUser($data->tellerId) : null;
        $teller .= "abc";
        $wedstrijd->tellers[$data->tellerIndex] = $teller;

        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
        $uscWedstrijd = Wedstrijd::GetWedstrijdWithMatchId($uscWedstrijden, $data->matchId);
        $wedstrijd->AppendInformation($uscWedstrijd);

        if ($wedstrijd->id === null) {
            $this->telFluitGateway->Insert($wedstrijd);
        } else {
            if ($wedstrijd->tellers[0] === null && $wedstrijd->tellers[1] === null && $wedstrijd->scheidsrechter === null) {
                $this->telFluitGateway->Delete($wedstrijd);
            } else {
                $this->telFluitGateway->Update($wedstrijd);
            }
        }
    }
}
