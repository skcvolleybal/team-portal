<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Persoon;
use TeamPortal\Gateways\FluitBeschikbaarheidGateway;
use TeamPortal\Gateways\JoomlaGateway;
use TeamPortal\Gateways\NevoboGateway;
use TeamPortal\Gateways\TelFluitGateway;
use UnexpectedValueException;

class GetScheidsrechters implements Interactor
{
    public function __construct(
        JoomlaGateway $joomlaGateway,
        TelFluitGateway $telFluitGateway,
        NevoboGateway $nevoboGateway,
        FluitBeschikbaarheidGateway $fluitBeschikbaarheidGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->fluitBeschikbaarheidGateway = $fluitBeschikbaarheidGateway;
    }

    public function Execute(object $data = null): array
    {
        if ($data->matchId === null) {
            throw new InvalidArgumentException("MatchId niet gezet");
        }

        $fluitWedstrijd = null;
        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
        foreach ($uscWedstrijden as $wedstrijd) {
            if ($wedstrijd->matchId == $data->matchId) {
                $fluitWedstrijd = $wedstrijd;
                break;
            }
        }
        if ($fluitWedstrijd === null) {
            throw new UnexpectedValueException("Wedstrijd met id $data->matchId niet gevonden");
        }

        if ($fluitWedstrijd->timestamp === null) {
            throw new UnexpectedValueException("Wedstrijd staat op het programma, maar heeft geen tijdstip");
        }

        $date = $fluitWedstrijd->timestamp;
        $wedstrijden = [];
        foreach ($uscWedstrijden as $wedstrijd) {
            if (DateFunctions::AreDatesEqual($wedstrijd->timestamp, $date)) {
                $wedstrijden[] = $wedstrijd;
            }
        }

        $fluitBeschikbaarheden = $this->fluitBeschikbaarheidGateway->GetAllBeschikbaarheden($date);
        $scheidsrechters = $this->telFluitGateway->GetScheidsrechters();

        $result = [
            new Beschikbaarheidssamenvatting("spelendeScheidsrechters"),
            new Beschikbaarheidssamenvatting("overigeScheidsrechters")
        ];

        foreach ($scheidsrechters as $scheidsrechter) {
            $wedstrijd = $scheidsrechter->team != null ? $scheidsrechter->team->GetWedstrijdOfTeam($wedstrijden) : null;
            $isBeschikbaar = $this->GetFluitbeschikbaarheid($scheidsrechter, $fluitBeschikbaarheden);

            $newScheidsrechter = new ScheidsrechterModel($scheidsrechter);
            $newScheidsrechter->eigenTijd = $wedstrijd ? DateFunctions::GetTime($wedstrijd->timestamp) : null;
            $newScheidsrechter->isBeschikbaar = $isBeschikbaar;

            $result[$wedstrijd !== null ? 0 : 1]->AddScheidsrechter($newScheidsrechter);
        }

        return $result;
    }

    private function GetFluitbeschikbaarheid(Persoon $scheidsrechter, array $fluitBeschikbaarheden)
    {
        foreach ($fluitBeschikbaarheden as $fluitBeschikbaarheid) {
            if ($fluitBeschikbaarheid->persoon->id == $scheidsrechter->id) {
                return $fluitBeschikbaarheid->isBeschikbaar;
            }
        }
        return null;
    }
}
