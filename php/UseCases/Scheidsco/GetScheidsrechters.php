<?php


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

    public function Execute(object $data): array
    {
        if ($data->matchId === null) {
            throw new InvalidArgumentException("MatchId niet gezet");
        }

        $fluitWedstrijd = null;
        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal('LDNUN');
        foreach ($uscWedstrijden as $wedstrijd) {
            if ($wedstrijd->matchId == $data->matchId) {
                $fluitWedstrijd = $wedstrijd;
                break;
            }
        }
        if ($fluitWedstrijd === null) {
            throw new UnexpectedValueException("Wedstrijd met id $matchId niet gevonden");
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
            $model = $this->MapToUsecaseModel($scheidsrechter, $isBeschikbaar, $wedstrijd);

            $result[$wedstrijd !== null ? 0 : 1]->AddScheidsrechter($model);
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

    private function MapToUsecaseModel(Persoon $scheidsrechter, ?bool $isBeschikbaar, ?Wedstrijd $wedstrijd)
    {
        return (object) [
            "id" => $scheidsrechter->id,
            "naam" => $scheidsrechter->naam,
            "niveau" => $scheidsrechter->niveau,
            "gefloten" => $scheidsrechter->aantalGeflotenWedstrijden,
            "team" => $scheidsrechter->team != null ? $scheidsrechter->team->GetShortNotation() : "Geen Team",
            "eigenTijd" => $wedstrijd ? DateFunctions::GetTime($wedstrijd->timestamp) : null,
            "isBeschikbaar" => $isBeschikbaar,
        ];
    }
}
