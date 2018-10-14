<?php
include_once 'IInteractor.php';
include_once 'JoomlaGateway.php';
include_once 'NevoboGateway.php';
include_once 'TelFluitGateway.php';
include_once 'ZaalwachtGateway.php';

class GetScheidscoOverzicht implements IInteractor
{
    private $nevoboGateway;
    private $joomlaGateway;
    private $fluitBeschikbaarheidGateway;
    private $telFluitGateway;
    private $zaalwachtGateway;

    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->telFluitGateway = new TelFluitGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->zaalwachtGateway = new ZaalwachtGateway($database);
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId == null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsScheidsco($userId)) {
            InternalServerError("Je bent (helaas) geen Scheidsco");
        }
        $overzicht = [];
        $uscProgramma = $this->nevoboGateway->GetProgrammaForSporthal('LDNUN');
        $uscProgramma = RemoveMatchesWithoutData($uscProgramma);

        $indeling = $this->telFluitGateway->GetIndeling();
        $zaalwachtIndeling = $this->zaalwachtGateway->GetZaalwachtIndeling();

        foreach ($uscProgramma as $wedstrijd) {
            $matchId = $wedstrijd['id'];
            $datum = GetDutchDate($wedstrijd['timestamp']);
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
                "tellers" => null,
            ];

            $wedstrijdIndeling = $this->GetWedstrijdIndeling($matchId, $indeling);
            if ($wedstrijdIndeling != null) {
                $newWedstrijd['tellers'] = $wedstrijdIndeling['tellers'];
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
            if ($indelingItem['matchId'] == $matchId) {
                return $indelingItem;
            }
        }
        return null;
    }
}
