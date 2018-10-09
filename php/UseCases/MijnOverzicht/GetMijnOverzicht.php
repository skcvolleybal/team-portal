<?php

include 'IInteractor.php';
include 'UserGateway.php';
include 'NevoboGateway.php';
include 'IndelingGateway.php';

class GetMijnOverzichtInteractor implements IInteractor
{
    public function __construct($database)
    {
        $this->userGateway = new UserGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->indelingGateway = new IndelingGateway($database);
    }

    private $nevoboGateway;

    public function Execute()
    {
        $userId = $this->userGateway->GetUserId();

        if ($userId === null) {
            UnauthorizedResult();
        }

        $team = $this->userGateway->GetTeam($userId);
        $coachTeam = $this->userGateway->GetCoachTeam($userId);

        $overzicht = [];

        $allUscMatches = $this->nevoboGateway->GetProgrammaForSporthal("LDNUN");

        $zaalwachten = $this->indelingGateway->GetZaalwachtForUserId($userId);
        foreach ($zaalwachten as $zaalwacht) {
            $overzichtItem = $this->MapFromZaalwacht($zaalwacht, $allUscMatches);
            $this->AddToOverzicht($overzicht, $overzichtItem);
        }

        $telbeurten = $this->indelingGateway->GetTelbeurten($userId);
        foreach ($telbeurten as $telbeurt) {
            $overzichtItem = $this->MapFromMatch($telbeurt, $allUscMatches, $team, $coachTeam, $userId);
            if ($overzichtItem != null) {
                $this->AddToOverzicht($overzicht, $overzichtItem);
            }
        }

        $fluitbeurten = $this->indelingGateway->GetFluitbeurten($userId);
        foreach ($fluitbeurten as $fluitbeurt) {
            $overzichtItem = $this->MapFromMatch($fluitbeurt, $allUscMatches, $team, $coachTeam, $userId);
            if ($overzichtItem != null) {
                $this->AddToOverzicht($overzicht, $overzichtItem);
            }
        }

        $speelWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);
        foreach ($speelWedstrijden as $speelWedstrijd) {
            $overzichtItem = $this->MapFromNevoboMatch($speelWedstrijd, $team, $coachTeam);
            if ($overzichtItem != null) {
                $this->AddToOverzicht($overzicht, $overzichtItem);
            }
        }

        if ($coachTeam != null) {
            $coachWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($coachTeam);
            foreach ($coachWedstrijden as $coachWedstrijd) {
                $overzichtItem = $this->MapFromNevoboMatch($coachWedstrijd, $team, $coachTeam);
                if ($overzichtItem != null) {
                    $this->AddToOverzicht($overzicht, $overzichtItem);
                }
            }
        }

        $isWebcie = $this->userGateway->IsWebcie($userId);

        echo json_encode(["overzicht" => $overzicht, "isWebcie" => $isWebcie]);
        exit;
    }

    private function MapFromMatch($match, $allUscMatches, $team, $coachTeam, $userId)
    {
        $uscMatch = $this->GetUscMatch($match['match_id'], $allUscMatches);
        if ($uscMatch == null || $uscMatch['timestamp'] == null) {
            return null;
        }
        return [
            "type" => "wedstrijd",
            "datum" => $uscMatch['timestamp']->format('Y-m-d'),
            "tijd" => $uscMatch['timestamp']->format('G:i'),
            "team1" => $uscMatch['team1'],
            "isTeam1" => $uscMatch['team1'] == $team,
            "isCoachTeam1" => $uscMatch['team1'] == $coachTeam,
            "team2" => $uscMatch['team2'],
            "isTeam2" => $uscMatch['team2'] == $team,
            "isCoachTeam2" => $uscMatch['team2'] == $coachTeam,
            "scheidsrechter" => $match['scheidsrechter'],
            "isScheidsrechter" => $match['scheidsrechter_id'] == $userId,
            "telteam" => $match['telteam'],
            "isTelteam" => $match['telteam'] == $team,
            "locatie" => $uscMatch['locatie'],
        ];
    }

    private function MapFromNevoboMatch($match, $team, $coachTeam)
    {
        if ($match['timestamp'] == null || $uscMatch['timestamp'] == null) {
            return null;
        }
        return [
            "type" => "wedstrijd",
            "datum" => $match['timestamp']->format('Y-m-d'),
            "tijd" => $match['timestamp']->format('G:i'),
            "team1" => $match['team1'],
            "isTeam1" => $match['team1'] == $team,
            "isCoachTeam1" => $match['team1'] == $coachTeam,
            "team2" => $match['team2'],
            "isTeam2" => $match['team2'] == $team,
            "isCoachTeam2" => $match['team2'] == $coachTeam,
            "locatie" => $match['locatie'],
        ];
    }

    private function MapFromZaalwacht($match)
    {
        return [
            "type" => "zaalwacht",
            "datum" => (new DateTime($match['date']))->format('Y-m-d'),
            "team" => $match['team'],
        ];
    }

    private function AddToOverzicht(&$overzicht, $newItem)
    {
        $newItemDatum = $newItem['datum'];
        $counter = 0;
        foreach ($overzicht as &$item) {
            if (strtotime($newItemDatum) == strtotime($item['datum'])) {
                $this->AddToTijdslot($item['tijdsloten'], $newItem);
                return;
            }

            if (strtotime($newItemDatum) < strtotime($item['datum'])) {
                $newDay = [
                    "datum" => (new DateTime($newItemDatum))->format("j F Y"),
                    "tijdsloten" => [$newItem],
                ];
                array_splice($overzicht, $counter, 0, [$newDay]);
                return;
            }
            $counter++;
        }
        $overzicht[] = [
            "datum" => (new DateTime($newItemDatum))->format("j F Y"),
            "tijdsloten" => [$newItem],
        ];
    }

    private function AddToTijdslot(&$tijdsloten, $newItem)
    {
        if ($newItem['type'] == "zaalwacht") {
            array_splice($tijdsloten, 0, 0, $newItem);
            return;
        }

        $counter = 0;
        foreach ($tijdsloten as $tijdslot) {
            if ($tijdslot['type'] != 'zaalwacht') {
                if ($newItem['tijd'] <= $tijdslot['tijd']) {
                    array_splice($tijdsloten, $counter, 0, [$newItem]);
                    return;
                }
            }
            $counter++;
        }
        $tijdsloten[] = $newItem;
    }

    private function GetUscMatch($matchId, $allUscMatches)
    {
        foreach ($allUscMatches as $match) {
            if ($match['id'] == $matchId) {
                return $match;
            }
        }

        return null;
    }
}
