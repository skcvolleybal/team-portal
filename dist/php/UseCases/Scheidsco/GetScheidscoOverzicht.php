<?php
include 'IInteractor.php';
include 'UserGateway.php';
include 'NevoboGateway.php';
include 'IndelingGateway.php';

class GetScheidscoOverzicht implements IInteractor
{
    private $nevoboGateway;
    private $userGateway;
    private $fluitBeschikbaarheidGateway;
    private $indelingGateway;
    private $uscCode = 'LDNUN';

    public function __construct($database)
    {
        $this->userGateway = new UserGateway($database);
        $this->indelingGateway = new IndelingGateway($database);
        $this->nevoboGateway = new NevoboGateway();
    }

    public function Execute()
    {
        $userId = $this->userGateway->GetUserId();
        if ($userId == null) {
            UnauthorizedResult();
        }

        if (!$this->userGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent (helaas) geen Scheidsco");
        }
        $overzicht = [];
        $uscProgramma = $this->nevoboGateway->GetProgrammaForSporthal($this->uscCode);
        $indeling = $this->indelingGateway->GetIndeling();
        $zaalwachtIndeling = $this->indelingGateway->GetZaalwachtIndeling();

        foreach ($uscProgramma as $wedstrijd) {
            $matchId = $wedstrijd['id'];
            $datum = $wedstrijd['timestamp']->format('j F Y');
            $date = $wedstrijd['timestamp']->format('Y-m-d');
            $tijd = $wedstrijd['timestamp']->format('G:i');
            $time = $wedstrijd['timestamp']->format('G:i:s');
            $team1 = $wedstrijd['team1'];
            $team2 = $wedstrijd['team2'];

            $i = $this->GetIndexOfDatum($overzicht, $datum);
            if ($i === null) {
                $overzicht[] = [
                    "datum" => $datum,
                    "date" => $date,
                    "speeltijden" => [],
                    "zaalwacht" => GetShortTeam($this->GetZaalwachtForDatum($zaalwachtIndeling, $date)),
                ];
                $i = count($overzicht) - 1;
            }
            $j = $this->GetIndexOfSpeeltijd($overzicht[$i]['speeltijden'], $tijd);
            if ($j === null) {
                $overzicht[$i]['speeltijden'][] = [
                    'tijd' => $tijd,
                    'time' => $time,
                    'wedstrijden' => [],
                ];
                $j = count($overzicht[$i]['speeltijden']) - 1;
            }

            $newWedstrijd = [
                "id" => $matchId,
                "teams" => $team1 . " - " . $team2,
                "scheidsrechter" => null,
                "telteam" => null,
            ];

            $wedstrijdIndeling = $this->GetWedstrijdIndeling($matchId, $indeling);
            if ($wedstrijdIndeling != null) {
                $newWedstrijd['telteam'] = GetShortTeam($wedstrijdIndeling['telteam']);
                $newWedstrijd['scheidsrechter'] = $wedstrijdIndeling['scheidsrechter'];
            }

            $overzicht[$i]['speeltijden'][$j]['wedstrijden'][] = $newWedstrijd;
        }

        exit(json_encode($overzicht));
    }

    private function GetIndexOfDatum($rooster, $datum)
    {
        for ($i = count($rooster) - 1; $i >= 0; $i--) {
            if ($rooster[$i]['datum'] == $datum) {
                return $i;
            }
        }
        return null;
    }

    private function GetIndexOfSpeeltijd($speeltijden, $tijd)
    {
        for ($i = count($speeltijden) - 1; $i >= 0; $i--) {
            if ($speeltijden[$i]['tijd'] == $tijd) {
                return $i;
            }
        }
        return null;
    }

    private function GetZaalwachtForDatum($zaalwachtIndeling, $date)
    {
        foreach ($zaalwachtIndeling as $zaalwacht) {
            if ($zaalwacht['date'] == $date) {
                return $zaalwacht['team'];
            }
        }
        return null;
    }

    private function GetWedstrijdIndeling($matchId, $indeling)
    {
        foreach ($indeling as $indelingItem) {
            if ($indelingItem['match_id'] == $matchId) {
                return $indelingItem;
            }
        }
        return null;
    }
}
