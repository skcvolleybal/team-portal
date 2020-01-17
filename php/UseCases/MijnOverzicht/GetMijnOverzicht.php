<?php

class MijnOverzicht implements Interactor
{
    public function __construct(
        JoomlaGateway $joomlaGateway,
        NevoboGateway $nevoboGateway,
        TelFluitGateway $telFluitGateway,
        ZaalwachtGateway $zaalwachtGateway,
        BarcieGateway $barcieGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute()
    {
        $user = $this->joomlaGateway->GetUser();
        $team = $this->joomlaGateway->GetTeam($user);
        $coachTeam = $this->joomlaGateway->GetCoachTeam($user);

        $overzicht = [];

        $allUscMatches = $this->nevoboGateway->GetProgrammaForVereniging("CKL9R53");

        $zaalwachten = $this->zaalwachtGateway->GetZaalwachtenOfUser($user);
        foreach ($zaalwachten as $zaalwacht) {
            $overzichtItem = $this->MapFromZaalwacht($zaalwacht, $allUscMatches);
            $this->AddToOverzicht($overzicht, $overzichtItem);
        }

        $telbeurten = $this->telFluitGateway->GetTelbeurten($user);
        foreach ($telbeurten as $telbeurt) {
            $overzichtItem = $this->MapFromMatch($telbeurt, $allUscMatches, $team, $coachTeam, $user);
            if ($overzichtItem) {
                $this->AddToOverzicht($overzicht, $overzichtItem);
            }
        }

        $fluitbeurten = $this->telFluitGateway->GetFluitbeurten($user);
        foreach ($fluitbeurten as $fluitbeurt) {
            $overzichtItem = $this->MapFromMatch($fluitbeurt, $allUscMatches, $team, $coachTeam, $user);
            if ($overzichtItem) {
                $this->AddToOverzicht($overzicht, $overzichtItem);
            }
        }

        $bardiensten = $this->barcieGateway->GetBardienstenForUser($user);
        foreach ($bardiensten as $dienst) {
            $overzichtItem = $this->MapFromBardienst($dienst);
            if ($overzichtItem) {
                $this->AddToOverzicht($overzicht, $overzichtItem);
            }
        }

        $speelWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($team);
        foreach ($speelWedstrijden as $speelWedstrijd) {
            $overzichtItem = $this->MapFromMatch($speelWedstrijd, $allUscMatches, $team, $coachTeam, $user);
            if ($overzichtItem) {
                $this->AddToOverzicht($overzicht, $overzichtItem);
            }
        }

        if ($coachTeam) {
            $coachWedstrijden = $this->nevoboGateway->GetWedstrijdenForTeam($coachTeam);
            foreach ($coachWedstrijden as $coachWedstrijd) {
                $overzichtItem = $this->MapFromMatch($coachWedstrijd, $allUscMatches, $team, $coachTeam, $user);
                if ($overzichtItem) {
                    $this->AddToOverzicht($overzicht, $overzichtItem);
                }
            }
        }

        return $overzicht;
    }

    private function MapFromMatch(Wedstrijd $match, array $allUscMatches, Team $team, Team $coachTeam, Persoon $user)
    {
        $uscMatch = $this->GetUscMatch($match->matchId, $allUscMatches);
        if ($uscMatch === null) {
            return null;
        }
        return (object) [
            "matchId" => $uscMatch->matchId,
            "type" => "wedstrijd",
            "date" => DateFunctions::GetYmdNotation($uscMatch->timestamp),
            "tijd" => DateFunctions::GetTime($uscMatch->timestamp),
            "team1" => $uscMatch->team1->naam,
            "isTeam1" => $uscMatch->team1->Equals($team),
            "isCoachTeam1" => $uscMatch->team1->Equals($coachTeam),
            "team2" => $uscMatch->team2->naam,
            "isTeam2" => $uscMatch->team2->Equals($team),
            "isCoachTeam2" => $uscMatch->team2->Equals($coachTeam),
            "scheidsrechter" => $match->scheidsrechter ? $match->scheidsrechter->naam : null,
            "isScheidsrechter" => $user->Equals($match->scheidsrechter),
            "tellers" => $match->telteam ? $match->telteam->GetShortNotation() : null,
            "isTellers" => $team->Equals($match->telteam),
            "locatie" => $uscMatch->locatie
        ];
    }

    private function MapFromBardienst(Bardienst $dienst)
    {
        return (object) [
            "type" => "bardienst",
            "date" => DateFunctions::GetYmdNotation($dienst->date),
            "shift" => $dienst->shift,
            "isBhv" => $dienst->isBhv
        ];
    }

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

    private function GetNewDateItem(string $date, $newItem)
    {
        $datetime = DateFunctions::CreateDateTime($date);
        return (object) [
            "datum" => DateFunctions::GetDutchDate($datetime),
            "date" => DateFunctions::GetYmdNotation($datetime),
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
            return $wedstrijd->type == "wedstrijd" && $wedstrijd->matchId == $newItem->matchId;
        });

        if (count($duplicates) > 0) {
            return;
        }

        $counter = 0;
        foreach ($tijdsloten as $tijdslot) {
            if (
                !in_array($tijdslot->type, ['zaalwacht', 'bardienst']) &&
                $newItem->tijd <= $tijdslot->tijd &&
                $newItem->matchId != $tijdslot->matchId
            ) {
                array_splice($tijdsloten, $counter, 0, [$newItem]);
                return;
            }
            $counter++;
        }
        $tijdsloten[] = $newItem;
    }

    private function GetUscMatch($matchId, $allUscMatches)
    {
        foreach ($allUscMatches as $match) {
            if ($match->matchId == $matchId) {
                return $match;
            }
        }

        return null;
    }
}
