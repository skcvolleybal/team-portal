<?php


class GetScheidsrechters implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->telFluitGateway = new TelFluitGateway($database);
        $this->nevoboGateway = new NevoboGateway($database);
        $this->fluitBeschikbaarheidGateway = new FluitBeschikbaarheidGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent (helaas) geen teamcoordinator");
        }

        $matchId = $data->matchId ?? null;
        if ($matchId == null) {
            throw new InvalidArgumentException("MatchId niet gezet");
        }

        $fluitWedstrijd = null;
        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal('LDNUN');
        foreach ($uscWedstrijden as $wedstrijd) {
            if ($wedstrijd->id == $matchId) {
                $fluitWedstrijd = $wedstrijd;
            }
        }
        if ($fluitWedstrijd == null) {
            throw new UnexpectedValueException("Wedstrijd met id $matchId niet gevonden");
        }
        if ($fluitWedstrijd->timestamp == null) {
            throw new UnexpectedValueException("Wedstrijd met id $matchId heeft geen speeldatum");
        }

        $date = $fluitWedstrijd->timestamp->format('Y-m-d');
        $time = $fluitWedstrijd->timestamp->format('G:i:s');

        $wedstrijdenWithSameDate = GetWedstrijdenWithDate($uscWedstrijden, $fluitWedstrijd->timestamp);
        $fluitBeschikbaarheden = $this->fluitBeschikbaarheidGateway->GetAllBeschikbaarheid($date, $time);
        $scheidsrechters = $this->telFluitGateway->GetScheidsrechters();

        $result = (object) [
            "spelendeScheidsrechters" => (object) [
                "Ja" => [],
                "Onbekend" => [],
                "Nee" => [],
            ], "overigeScheidsrechters" => (object) [
                "Ja" => [],
                "Onbekend" => [],
                "Nee" => [],
            ]
        ];
        foreach ($scheidsrechters as $scheidsrechter) {
            $wedstrijd = GetWedstrijdOfTeam($wedstrijdenWithSameDate, $scheidsrechter->team);
            $isBeschikbaar = $this->GetFluitbeschikbaarheid($scheidsrechter, $fluitBeschikbaarheden);
            $type = $wedstrijd ? "spelendeScheidsrechters" : "overigeScheidsrechters";

            $result->$type->$isBeschikbaar[] = $this->MapToUsecaseModel($scheidsrechter, $isBeschikbaar, $wedstrijd, $fluitWedstrijd);
        }
        exit(json_encode($result));
    }

    private function GetFluitbeschikbaarheid($scheidsrechter, $fluitBeschikbaarheden)
    {
        foreach ($fluitBeschikbaarheden as $fluitBeschikbaarheid) {
            if ($fluitBeschikbaarheid->user_id == $scheidsrechter->id) {
                return $fluitBeschikbaarheid->is_beschikbaar;
            }
        }
        return "Onbekend";
    }

    private function MapToUsecaseModel($scheidsrechter, $isBeschikbaar, $wedstrijd)
    {
        return (object) [
            "naam" => $scheidsrechter->naam,
            "niveau" => $scheidsrechter->niveau,
            "gefloten" => $scheidsrechter->gefloten,
            "team" => GetShortTeam($scheidsrechter->team) ?? "Geen Team",
            "eigenTijd" => $wedstrijd && $wedstrijd->timestamp ? $wedstrijd->timestamp->format("G:i") : null,
            "isMogelijk" => $isBeschikbaar,
        ];
    }
}
