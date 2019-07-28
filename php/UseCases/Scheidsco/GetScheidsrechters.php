<?php

include_once 'IInteractorWithData.php';
include_once 'JoomlaGateway.php';
include_once 'FluitBeschikbaarheidGateway.php';
include_once 'TelFluitGateway.php';
include_once 'NevoboGateway.php';
include_once 'ScheidscoFunctions.php';

class GetScheidsrechters implements IInteractorWithData
{
    private $telFluitGateway;
    private $joomlaGateway;
    private $nevoboGateway;
    private $fluitBeschikbaarheidGateway;

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

        if (!$this->joomlaGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent (helaas) geen Scheidsco");
        }

        $matchId = $data->matchId ?? null;
        if ($matchId == null) {
            InternalServerError("MatchId niet gezet");
        }

        $fluitWedstrijd = null;
        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal('LDNUN');
        foreach ($uscWedstrijden as $wedstrijd) {
            if ($wedstrijd['id'] == $matchId) {
                $fluitWedstrijd = $wedstrijd;
            }
        }
        if ($fluitWedstrijd == null) {
            InternalServerError("Wedstrijd met id $matchId niet gevonden");
        }

        $date = $fluitWedstrijd['timestamp']->format('Y-m-d');
        $time = $fluitWedstrijd['timestamp']->format('G:i:s');

        $wedstrijdenWithSameDate = GetWedstrijdenWithDate($uscWedstrijden, $fluitWedstrijd['timestamp']);
        $fluitBeschikbaarheden = $this->fluitBeschikbaarheidGateway->GetAllBeschikbaarheid($date, $time);
        $scheidsrechters = $this->telFluitGateway->GetScheidsrechters();

        $result = [
            "spelendeScheidsrechters" => [
                "Ja" => [],
                "Onbekend" => [],
                "Nee" => [],
            ], "overigeScheidsrechters" => [
                "Ja" => [],
                "Onbekend" => [],
                "Nee" => [],
            ]
        ];
        foreach ($scheidsrechters as $scheidsrechter) {
            $wedstrijd = GetWedstrijdOfTeam($wedstrijdenWithSameDate, $scheidsrechter['team']);
            $fluitBeschikbaarheid = $this->GetFluitbeschikbaarheid($scheidsrechter, $fluitBeschikbaarheden);
            $type = $wedstrijd ? "spelendeScheidsrechters" : "overigeScheidsrechters";

            $result[$type][$fluitBeschikbaarheid][] = $this->MapToUsecaseModel($scheidsrechter, $fluitBeschikbaarheid, $wedstrijd, $fluitWedstrijd);
        }
        exit(json_encode($result));
    }

    private function GetFluitbeschikbaarheid($scheidsrechter, $fluitBeschikbaarheden)
    {
        foreach ($fluitBeschikbaarheden as $fluitBeschikbaarheid) {
            if ($fluitBeschikbaarheid['user_id'] == $scheidsrechter['id']) {
                return $fluitBeschikbaarheid['beschikbaarheid'];
            }
        }
        return "Onbekend";
    }

    private function MapToUsecaseModel($scheidsrechter, $fluitBeschikbaarheid, $wedstrijd = null, $fluitWedstrijd = null)
    {
        return [
            "naam" => $scheidsrechter['naam'],
            "niveau" => $scheidsrechter['niveau'],
            "gefloten" => $scheidsrechter['gefloten'],
            "team" => GetShortTeam($scheidsrechter['team']) ?? "Geen Team",
            "eigenTijd" => $wedstrijd['timestamp'] ? $wedstrijd['timestamp']->format("G:i") : null,
            "isMogelijk" => $fluitBeschikbaarheid,
        ];
    }
}
