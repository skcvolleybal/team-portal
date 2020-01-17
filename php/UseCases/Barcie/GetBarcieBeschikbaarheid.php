<?php


class GetBarcieBeschikbaarheid implements Interactor
{
    public function __construct(
        NevoboGateway $nevoboGateway,
        BarcieGateway $barcieGateway,
        JoomlaGateway $joomlaGateway,
        BarcieBeschikbaarheidHelper $barcieBeschikbaarheidHelper
    ) {
        $this->nevoboGateway = $nevoboGateway;
        $this->barcieGateway = $barcieGateway;
        $this->joomlaGateway = $joomlaGateway;
        $this->barcieBeschikbaarheidHelper = $barcieBeschikbaarheidHelper;
    }

    public function Execute(): array
    {
        $user = $this->joomlaGateway->GetUser();
        $team = $this->joomlaGateway->GetTeam($user);
        $coachTeam = $this->joomlaGateway->GetCoachTeam($user);

        $alleWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($team);
        $alleCoachWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($coachTeam);

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
                "eigenWedstrijden" => $this->MapToUsecase($wedstrijden, $team, $coachTeam),
                "isMogelijk" => $this->barcieBeschikbaarheidHelper->isMogelijk($wedstrijden),
            ];
        }

        return $response;
    }

    private function MapToUsecase(array $wedstrijden, ?Team $team, ?Team $coachTeam)
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            $result[] = (object) [
                "datum" => DateFunctions::GetDutchDate($wedstrijd->timestamp),
                "tijd" => $wedstrijd->timestamp->format('H:i'),
                "team1" => $wedstrijd->team1->naam,
                "isTeam1" => $wedstrijd->team1->Equals($team),
                "isCoachTeam1" => $wedstrijd->team1->Equals($coachTeam),
                "team2" => $wedstrijd->team2->naam,
                "isTeam2" => $wedstrijd->team2->Equals($team),
                "isCoachTeam2" => $wedstrijd->team2->Equals($coachTeam),
                "locatie" => $wedstrijd->GetShortLocatie(),
            ];
        }
        return $result;
    }

    private function GetBeschikbaarheid(array $beschikbaarheden, DateTime $date)
    {
        foreach ($beschikbaarheden as $beschikbaarheid) {
            if ($beschikbaarheid->date == $date) {
                return $beschikbaarheid->isBeschikbaar;
            }
        }

        return null;
    }
}
