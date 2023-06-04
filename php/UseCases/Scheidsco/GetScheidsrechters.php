<?php

namespace TeamPortal\UseCases;

use InvalidArgumentException;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Persoon;
use TeamPortal\Gateways\BeschikbaarheidGateway;
use TeamPortal\Gateways\WordPressGateway;
use TeamPortal\Gateways\NevoboGateway;
use TeamPortal\Gateways\TelFluitGateway;
use UnexpectedValueException;

class GetScheidsrechters implements Interactor
{
    public function __construct(
        WordPressGateway $wordPressGateway,
        TelFluitGateway $telFluitGateway,
        NevoboGateway $nevoboGateway,
        BeschikbaarheidGateway $beschikbaarheidGateway
    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->beschikbaarheidGateway = $beschikbaarheidGateway;
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

        $fluitBeschikbaarheden = $this->beschikbaarheidGateway->GetAllBeschikbaarheden($date);
        $scheidsrechters = $this->telFluitGateway->GetScheidsrechters();

        $result = [
            new Beschikbaarheidssamenvatting("spelendeScheidsrechters"),
            new Beschikbaarheidssamenvatting("overigeScheidsrechters")
        ];

        foreach ($scheidsrechters as $scheidsrechter) {
            $wedstrijd = $scheidsrechter->team != null ? $scheidsrechter->team->GetWedstrijdOfTeam($wedstrijden) : null;
            $isBeschikbaar = $this->GetBeschikbaarheid($scheidsrechter, $fluitBeschikbaarheden);

            $newScheidsrechter = new ScheidsrechterModel($scheidsrechter);
            $newScheidsrechter->eigenTijd = $wedstrijd ? DateFunctions::GetTime($wedstrijd->timestamp) : null;
            $newScheidsrechter->isBeschikbaar = $isBeschikbaar;

            $result[$wedstrijd !== null ? 0 : 1]->AddScheidsrechter($newScheidsrechter);
        }

        return $result;
    }

    private function GetBeschikbaarheid(Persoon $scheidsrechter, array $beschikbaarheden)
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid->persoon->id == $scheidsrechter->id) {
                return $beschikbaarheid->isBeschikbaar;
            }
        }
        return null;
    }
}
