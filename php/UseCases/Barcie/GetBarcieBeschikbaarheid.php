<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities;
use TeamPortal\Gateways;

class GetBarcieBeschikbaarheid implements Interactor
{
    public function __construct(
        Gateways\NevoboGateway $nevoboGateway,
        Gateways\BarcieGateway $barcieGateway,
        Gateways\JoomlaGateway $joomlaGateway
    ) {
        $this->nevoboGateway = $nevoboGateway;
        $this->barcieGateway = $barcieGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null): array
    {
        $user = $this->joomlaGateway->GetUser();

        $alleWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($user->team);
        $alleCoachWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($user->coachteam);

        $bardagen = $this->barcieGateway->GetBardagen();
        $beschikbaarheden = $this->barcieGateway->GetBeschikbaarheden($user);

        $response = [];
        foreach ($bardagen as $bardag) {
            $eigenWedstrijden = array_filter($alleWedstrijden, function ($wedstrijd) use ($bardag) {
                return $wedstrijd->timestamp && DateFunctions::AreDatesEqual($wedstrijd->timestamp, $bardag->date);
            });

            $coachWedstrijden = array_filter($alleCoachWedstrijden, function ($wedstrijd) use ($bardag) {
                return $wedstrijd->timestamp && DateFunctions::AreDatesEqual($wedstrijd->timestamp, $bardag->date);
            });

            $wedstrijden = array_merge($eigenWedstrijden, $coachWedstrijden);

            $isBeschikbaar = $this->GetBeschikbaarheid($beschikbaarheden, $bardag->date);

            $response[] = (object) [
                "datum" => DateFunctions::GetDutchDate($bardag->date),
                "date" => DateFunctions::GetYmdNotation($bardag->date),
                "beschikbaarheid" => $isBeschikbaar,
                "eigenWedstrijden" => $this->MapToUsecase($wedstrijden, $user->team, $user->coachteam),
                "isMogelijk" => Entities\Barbeschikbaarheid::IsMogelijk($wedstrijden),
            ];
        }

        return $response;
    }

    private function MapToUsecase(array $wedstrijden, ?Entities\Team $team, ?Entities\Team $coachteam)
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            $result[] = (object) [
                "datum" => DateFunctions::GetDutchDate($wedstrijd->timestamp),
                "tijd" => $wedstrijd->timestamp->format('H:i'),
                "team1" => $wedstrijd->team1->naam,
                "isTeam1" => $wedstrijd->team1->Equals($team),
                "isCoachTeam1" => $wedstrijd->team1->Equals($coachteam),
                "team2" => $wedstrijd->team2->naam,
                "isTeam2" => $wedstrijd->team2->Equals($team),
                "isCoachTeam2" => $wedstrijd->team2->Equals($coachteam),
                "locatie" => $wedstrijd->GetShortLocatie(),
            ];
        }
        return $result;
    }

    private function GetBeschikbaarheid(array $beschikbaarheden, \DateTime $date)
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid->date == $date) {
                return $beschikbaarheid->isBeschikbaar;
            }
        }

        return null;
    }
}
