<?php

include_once 'IInteractor.php';
include_once 'JoomlaGateway.php';
include_once 'NevoboGateway.php';
include_once 'TelFluitGateway.php';
include_once 'ZaalwachtGateway.php';

class GetMijnOverzichtInteractor implements IInteractor
{
    public function __construct($database)
    {
        $this->joomlaGateway = new JoomlaGateway($database);
        $this->nevoboGateway = new NevoboGateway();
        $this->telFluitGateway = new TelFluitGateway($database);
        $this->zaalwachtGateway = new ZaalwachtGateway($database);
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();

        if ($userId === null) {
            UnauthorizedResult();
        }

        $team = $this->joomlaGateway->GetTeam($userId);
        $coachTeam = $this->joomlaGateway->GetCoachTeam($userId);

        $overzicht = [];

        $allUscMatches = $this->nevoboGateway->GetProgrammaForVereniging("CKL9R53");

        $allUscMatches = RemoveMatchesWithoutData($allUscMatches);

        $zaalwachten = $this->zaalwachtGateway->GetZaalwachtForUserId($userId);
        foreach ($zaalwachten as $zaalwacht) {
            $overzichtItem = $this->MapFromZaalwacht($zaalwacht, $allUscMatches);
            $this->AddToOverzicht($overzicht, $overzichtItem);
        }

        $telbeurten = $this->telFluitGateway->GetTelbeurten($userId);
        foreach ($telbeurten as $telbeurt) {
            $overzichtItem = $this->MapFromMatch($telbeurt, $allUscMatches, $team, $coachTeam, $userId);
            if ($overzichtItem) {
                $this->AddToOverzicht($overzicht, $overzichtItem);
            }
        }

        $fluitbeurten = $this->telFluitGateway->GetFluitbeurten($userId);
        foreach ($fluitbeurten as $fluitbeurt) {
            $overzichtItem = $this->MapFromMatch($fluitbeurt, $allUscMatches, $team, $coachTeam, $userId);
            if ($overzichtItem) {
                $this->AddToOverzicht($overzicht, $overzichtItem);
            }
        }

        $speelWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);
        foreach ($speelWedstrijden as $speelWedstrijd) {
            $overzichtItem = $this->MapFromMatch($speelWedstrijd, $allUscMatches, $team, $coachTeam, $userId);
            if ($overzichtItem) {
                $this->AddToOverzicht($overzicht, $overzichtItem);
            }
        }

        if ($coachTeam) {
            $coachWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($coachTeam);
            foreach ($coachWedstrijden as $coachWedstrijd) {
                $overzichtItem = $this->MapFromMatch($coachWedstrijd, $allUscMatches, $team, $coachTeam, $userId);
                if ($overzichtItem) {
                    $this->AddToOverzicht($overzicht, $overzichtItem);
                }
            }
        }

        echo json_encode($overzicht);
        exit;
    }

    private function MapFromMatch($match, $allUscMatches, $team, $coachTeam, $userId)
    {
        $uscMatch = $this->GetUscMatch($match->id, $allUscMatches);
        if ($uscMatch == null) {
            return null;
        }
        return (object) [
            "id" => $uscMatch->id,
            "type" => "wedstrijd",
            "date" => $uscMatch->timestamp->format('Y-m-d'),
            "tijd" => $uscMatch->timestamp->format('G:i'),
            "team1" => $uscMatch->team1,
            "isTeam1" => $uscMatch->team1 == $team,
            "isCoachTeam1" => $uscMatch->team1 == $coachTeam,
            "team2" => $uscMatch->team2,
            "isTeam2" => $uscMatch->team2 == $team,
            "isCoachTeam2" => $uscMatch->team2 == $coachTeam,
            "scheidsrechter" => $match->scheidsrechter ?? null,
            "isScheidsrechter" => ($match->scheidsrechterId ?? null) == $userId,
            "tellers" => GetShortTeam(($match->tellers ?? null)),
            "isTellers" => ($match->tellers ?? null) == $team,
            "locatie" => $uscMatch->locatie,
        ];
    }

    // private function MapFromNevoboMatch($match, $team, $coachTeam)
    // {
    //     $uscMatch = $this->GetUscMatch($match->matchId, $allUscMatches);
    //     if ($uscMatch == null) {
    //         return null;
    //     }
    //     return [
    //         "id" => $match->id,
    //         "type" => "wedstrijd",
    //         "date" => $match->timestamp->format('Y-m-d'),
    //         "tijd" => $match->timestamp->format('G:i'),
    //         "team1" => $match->team1,
    //         "isTeam1" => $match->team1 == $team,
    //         "isCoachTeam1" => $match->team1 == $coachTeam,
    //         "team2" => $match->team2,
    //         "isTeam2" => $match->team2 == $team,
    //         "isCoachTeam2" => $match->team2 == $coachTeam,
    //         "locatie" => $match->locatie,
    //     ];
    // }

    private function MapFromZaalwacht($match)
    {
        return (object) [
            "type" => "zaalwacht",
            "date" => (new DateTime($match->date))->format('Y-m-d'),
            "team" => $match->team,
        ];
    }

    private function AddToOverzicht(&$overzicht, $newItem)
    {
        $newItemDate = $newItem->date;
        $counter = 0;
        foreach ($overzicht as &$item) {
            if ($newItemDate == $item->date) {
                $this->AddToTijdslot($item->tijdsloten, $newItem);
                return;
            }

            if ($newItemDate < $item->date) {
                $newDay = $this->GetNewDateItem($newItemDate, $newItem);
                array_splice($overzicht, $counter, 0, [$newDay]);
                return;
            }
            $counter++;
        }
        $overzicht[] = $this->GetNewDateItem($newItemDate, $newItem);
    }

    private function GetNewDateItem($date, $newItem)
    {
        $datetime = new DateTime($date);
        return (object) [
            "datum" => GetDutchDate($datetime),
            "date" => $datetime->format('Y-m-d'),
            "tijdsloten" => [$newItem],
        ];
    }

    private function AddToTijdslot(&$tijdsloten, $newItem)
    {
        if ($newItem->type == "zaalwacht") {
            array_splice($tijdsloten, 0, 0, $newItem);
            return;
        }

        $duplicates = array_filter($tijdsloten, function ($wedstrijd) use ($newItem) {
            return $wedstrijd->type == "wedstrijd" && $wedstrijd->id == $newItem->id;
        });

        if (count($duplicates) > 0) {
            return;
        }

        $counter = 0;
        foreach ($tijdsloten as $tijdslot) {
            if ($tijdslot->type != 'zaalwacht') {
                if ($newItem->tijd <= $tijdslot->tijd) {
                    if ($newItem->id != $tijdslot->id) {
                        array_splice($tijdsloten, $counter, 0, [$newItem]);
                    }
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
            if ($match->id == $matchId) {
                return $match;
            }
        }

        return null;
    }
}
