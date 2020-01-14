<?php


class GetBarcieBeschikbaarheid extends GetNevoboMatchByDate implements IInteractor
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
        $userId = $this->joomlaGateway->GetUserId();

        if ($userId === null) {
            throw new UnauthorizedException();
        }

        $barcielid = $this->joomlaGateway->GetUser($userId);
        $team = $this->joomlaGateway->GetTeam($userId);
        $coachTeam = $this->joomlaGateway->GetCoachTeam($userId);

        $alleWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($team);
        $alleCoachWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($coachTeam);

        $barciedagen = $this->barcieGateway->GetBarciedagen();
        $beschikbaarheden = $this->barcieGateway->GetBeschikbaarheden($barcielid);

        $response = [];
        foreach ($barciedagen as $barciedag) {
            $eigenWedstrijden = array_filter($alleWedstrijden, function ($wedstrijd) use ($barciedag) {
                return $wedstrijd->timestamp && DateFunctions::GetYmdNotation($wedstrijd->timestamp) == DateFunctions::GetYmdNotation($barciedag);
            });

            $coachWedstrijden = array_filter($alleCoachWedstrijden, function ($wedstrijd) use ($barciedag) {
                return $wedstrijd->timestamp && DateFunctions::GetYmdNotation($wedstrijd->timestamp) == DateFunctions::GetYmdNotation($barciedag);
            });

            $wedstrijden = array_merge($eigenWedstrijden, $coachWedstrijden);

            $isBeschikbaar = $this->GetBeschikbaarheid($beschikbaarheden, $barciedag);

            $response[] = (object) [
                "datum" => DateFunctions::GetDutchDate($barciedag),
                "date" => DateFunctions::GetYmdNotation($barciedag),
                "beschikbaarheid" => $isBeschikbaar,
                "eigenWedstrijden" => $this->MapToUsecase($wedstrijden, $team, $coachTeam),
                "isMogelijk" => $this->barcieBeschikbaarheidHelper->isMogelijk($wedstrijden),
            ];
        }

        return $response;
    }

    private function MapToUsecase(array $wedstrijden, Team $team, Team $coachTeam)
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
