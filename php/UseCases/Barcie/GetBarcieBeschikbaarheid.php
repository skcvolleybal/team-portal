<?php

namespace TeamPortal\UseCases;

use DateTime;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Barbeschikbaarheid;
use TeamPortal\Entities\Persoon;
use TeamPortal\Gateways\BarcieGateway;
use TeamPortal\Gateways\WordPressGateway;
use TeamPortal\Gateways\NevoboGateway;

class GetBarcieBeschikbaarheid implements Interactor
{
    public function __construct(
        NevoboGateway $nevoboGateway,
        BarcieGateway $barcieGateway,
        WordPressGateway $wordPressGateway
    ) {
        $this->nevoboGateway = $nevoboGateway;
        $this->barcieGateway = $barcieGateway;
        $this->wordPressGateway = $wordPressGateway;
    }

    public function Execute(object $data = null): array
    {
        $user = $this->wordPressGateway->GetUser();

        $alleWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($user->team);

        $alleCoachWedstrijden = [];
        foreach ($user->coachteams as $team) {
            $coachwedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($team);
            $alleCoachWedstrijden = array_merge($alleCoachWedstrijden, $coachwedstrijden);
        }

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

            $barciebeschikbaarheid = new BarbeschikbaarheidModel;

            $barciebeschikbaarheid->datum = DateFunctions::GetDutchDate($bardag->date);
            $barciebeschikbaarheid->date = DateFunctions::GetYmdNotation($bardag->date);
            $barciebeschikbaarheid->beschikbaarheid = $isBeschikbaar;
            $barciebeschikbaarheid->eigenWedstrijden = $this->MapToUsecase($wedstrijden, $user);
            $barciebeschikbaarheid->isMogelijk = Barbeschikbaarheid::IsMogelijk($wedstrijden);

            $response[] = $barciebeschikbaarheid;
        }

        return $response;
    }

    private function MapToUsecase(array $wedstrijden, Persoon $user)
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            $newWedstrijd = new WedstrijdModel($wedstrijd);
            $newWedstrijd->SetPersonalInformation($user);
            $result[] = $newWedstrijd;
        }
        return $result;
    }

    private function GetBeschikbaarheid(array $beschikbaarheden, DateTime $date): ?bool
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid->date == $date) {
                return $beschikbaarheid->isBeschikbaar;
            }
        }

        return null;
    }
}
