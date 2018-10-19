<?php
include_once 'IInteractor.php';
include_once 'JoomlaGateway.php';
include_once 'AanwezigheidGateway.php';
include_once 'TelFluitGateway.php';
include_once 'ZaalwachtGateway.php';

class GetCoachAanwezigheid implements IInteractor
{

    public function __construct($database)
    {
        $this->aanwezigheidGateway = new AanwezigheidGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->telFluitGateway = new TelFluitGateway($database);
        $this->zaalwachtGateway = new ZaalwachtGateway($database);
        $this->nevoboGateway = new NevoboGateway();
    }

    public function Execute()
    {
        $this->userId = $this->joomlaGateway->GetUserId();
        if ($this->userId === null) {
            UnauthorizedResult();
        }

        $this->team = $this->joomlaGateway->GetTeam($this->userId);
        $this->coachTeam = $this->joomlaGateway->GetCoachTeam($this->userId);
        if ($this->coachTeam == null) {
            InternalServerError("Jij hebt geen coachteam");
        }

        $coachWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($this->coachTeam);

        $eigenWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($this->team);
        $fluitEnTelbeurten = $this->telFluitGateway->GetFluitEnTelbeurten($this->userId);
        $zaalwachten = $this->zaalwachtGateway->GetZaalwachtForUserId($this->userId);
        $aanwezigheden = $this->aanwezigheidGateway->GetAanwezigheden($this->userId);
        $this->uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal('LDNUN');

        $response = [];
        foreach ($coachWedstrijden as $wedstrijd) {
            $newItem = $this->MapWedstrijdToUsecase($wedstrijd);
            $newItem['aanwezigheid'] = $this->GetAanwezigheidForWedstrijd($wedstrijd, $aanwezigheden);
            $newItem['eigenWedstrijden'] = [];

            // Zaalwachten eerst, want die moeten bovenaan komen te staan
            $filteredZaalwachten = array_filter($zaalwachten, function ($zaalwacht) use ($wedstrijd) {
                return $wedstrijd['timestamp'] && $zaalwacht['date'] == $wedstrijd['timestamp']->format('Y-m-d');
            });
            foreach ($filteredZaalwachten as $zaalwacht) {
                $newItem["zaalwacht"] = $zaalwacht['team'];
            }

            $filteredEigenWedstrijden = array_filter($coachWedstrijden, function ($eigenWedstrijd) use ($wedstrijd) {
                return $eigenWedstrijd['timestamp'] && $eigenWedstrijd['timestamp'] && $eigenWedstrijd['timestamp'] == $wedstrijd['timestamp']->format('Y-m-d');
            });
            foreach ($filteredEigenWedstrijden as $eigenWedstrijd) {
                $newItem["eigenWedstrijden"][] = $this->MapWedstrijdToUsecase($eigenWedstrijd);
            }

            $filteredFluitEnTelbeurten = array_filter($fluitEnTelbeurten, function ($fluitEnTelbeurt) use ($wedstrijd) {
                if ($wedstrijd['timestamp'] == null) {
                    return false;
                }
                $matchIds = $this->GetMatchIdsByDate($wedstrijd['timestamp']->format('Y-m-d'));
                return in_array($fluitEnTelbeurt['matchId'], $matchIds);
            });
            foreach ($filteredFluitEnTelbeurten as $telOfFluitBeurt) {
                $newItem["eigenWedstrijden"][] = $this->MapFluitOfTelBeurtToUsecase($telOfFluitBeurt);
            }

            $response[] = $newItem;
        }

        exit(json_encode(["wedstrijden" => $response]));
    }

    private function GetAanwezigheidForWedstrijd($wedstrijd, $aanwezigheden)
    {
        foreach ($aanwezigheden as $aanwezigheid) {
            if ($aanwezigheid['matchId'] == $wedstrijd['id']) {
                return $aanwezigheid['aanwezigheid'];
            }
        }
        return "Misschien";
    }

    private function GetUscWedstrijd($matchId)
    {
        if ($matchId !== null) {
            foreach ($this->uscWedstrijden as $wedstrijd) {
                if ($wedstrijd['id'] == $matchId) {
                    return $wedstrijd;
                }
            }
        }
        return null;
    }

    private function GetMatchIdsByDate($date)
    {
        $result = [];
        foreach ($this->uscWedstrijden as $wedstrijd) {
            if ($wedstrijd['timestamp'] && $wedstrijd['timestamp']->format('Y-m-d') == $date) {
                $result[] = $wedstrijd['id'];
            }
        }

        return $result;
    }

    private function MapWedstrijdToUsecase($wedstrijd)
    {
        return [
            "id" => $wedstrijd['id'],
            "type" => "wedstrijd",
            "date" => $wedstrijd['timestamp']->format('Y-m-d'),
            "datum" => GetDutchDate($wedstrijd['timestamp']),
            "tijd" => $wedstrijd['timestamp']->format('G:i'),
            "team1" => $wedstrijd['team1'],
            "isTeam1" => $wedstrijd['team1'] == $this->team,
            "isCoachTeam1" => $wedstrijd['team1'] == $this->coachTeam,
            "team2" => $wedstrijd['team2'],
            "isTeam2" => $wedstrijd['team2'] == $this->team,
            "isCoachTeam2" => $wedstrijd['team2'] == $this->coachTeam,
            "locatie" => GetShortLocatie($wedstrijd['locatie']),
        ];
    }

    private function MapFluitOfTelBeurtToUsecase($telOfFluitBeurt)
    {
        $wedstrijd = $this->GetUscWedstrijd($telOfFluitBeurt['matchId']);
        if ($wedstrijd == null) {
            return null;
        }

        return [
            "id" => $wedstrijd['id'],
            "type" => "wedstrijd",
            "date" => $wedstrijd['timestamp']->format('Y-m-d'),
            "tijd" => $wedstrijd['timestamp']->format('G:i'),
            "team1" => $wedstrijd['team1'],
            "team2" => $wedstrijd['team2'],
            "scheidsrechter" => $telOfFluitBeurt['scheidsrechterId'] == $this->userId ? $telOfFluitBeurt['scheidsrechter'] : null,
            "isScheidsrechter" => $telOfFluitBeurt['scheidsrechterId'] == $this->userId,
            "tellers" => $telOfFluitBeurt['tellers'] == $this->team ? GetShortTeam($telOfFluitBeurt['tellers']) : null,
            "isTellers" => $telOfFluitBeurt['tellers'] == $this->team,
            "locatie" => GetShortLocatie($wedstrijd['locatie']),
        ];
    }
}
