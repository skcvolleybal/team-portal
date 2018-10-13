<?php
include 'IInteractorWithData.php';
include 'JoomlaGateway.php';
include 'FluitBeschikbaarheidGateway.php';
include 'TelFluitGateway.php';
include 'NevoboGateway.php';
include 'ScheidscoFunctions.php';

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
        $this->fluitBeschikbaarheidGateway = new FluitBeschikbaarheid($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId == null) {
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
            ]];
        foreach ($scheidsrechters as $scheidsrechter) {
            $wedstrijd = GetWedstrijdOfTeam($wedstrijdenWithSameDate, $scheidsrechter['team']);
            $fluitBeschikbaarheid = $this->GetFluitbeschikbaarheid($scheidsrechter, $fluitBeschikbaarheden);
            $type = $wedstrijd != null ? "spelendeScheidsrechters" : "overigeScheidsrechters";

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

        $team = $scheidsrechter['team'];
        $niveau = empty($scheidsrechter['niveau']) ? 'X' : $scheidsrechter['niveau'];

        if ($team != null) {
            $team = $team[0] . $team[6];
        } else {
            $team = 'Geen Team';
        }

        $eigenTijd = null;
        $isMogelijk = true;
        if ($wedstrijd != null && $fluitWedstrijd != null && $wedstrijd['timestamp'] != null && $fluitWedstrijd['timestamp']) {
            $interval = $wedstrijd['timestamp']->diff($fluitWedstrijd['timestamp']);
            $verschil = $interval->h;
            $eigenTijd = $wedstrijd['timestamp']->format("G:i");
            $isMogelijk = $verschil == 0 ? false : ($verschil == 2 ? true : null);
        }

        if (isset($fluitBeschikbaarheid['beschikbaarheid'])) {
            $isMogelijk = $fluitBeschikbaarheid['beschikbaarheid'] == "Ja" ? true : false;
        }

        return [
            "naam" => $scheidsrechter['naam'],
            "niveau" => $niveau,
            "gefloten" => $scheidsrechter['gefloten'],
            "team" => $team,
            "beschikbaarheid" => $isMogelijk,
            "eigenTijd" => $eigenTijd,
            "isMogelijk" => $isMogelijk,
        ];
    }
}
