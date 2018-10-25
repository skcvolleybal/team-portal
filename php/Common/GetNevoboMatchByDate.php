<?php
abstract class GetNevoboMatchByDate
{
    public function GetMatchesByDate($wedstrijden, $date)
    {
        $result = [];
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd['timestamp'] && $wedstrijd['timestamp']->format("Y-m-d") == $date) {
                $result[] = $wedstrijd;
            }
        }
        return $result;
    }
}

abstract class MapFromWedstrijd
{
    public function MapFromMatch($wedstrijd)
    {
        $skcMatch = null;
        foreach ($this->allSkcWedstrijden as $skcWedstrijd) {
            if ($skcWedstrijd['id'] == $wedstrijd['id']) {

            }
        }
        if ($skcMatch === null) {
            return null;
        }
        return [
            "id" => $skcWedstrijd['id'],
            "type" => "wedstrijd",
            "date" => $skcWedstrijd['timestamp']->format('Y-m-d'),
            "tijd" => $skcWedstrijd['timestamp']->format('G:i'),
            "team1" => $skcWedstrijd['team1'],
            "isTeam1" => $skcWedstrijd['team1'] == $team,
            "isCoachTeam1" => $skcWedstrijd['team1'] == $coachTeam,
            "team2" => $skcWedstrijd['team2'],
            "isTeam2" => $skcWedstrijd['team2'] == $team,
            "isCoachTeam2" => $skcWedstrijd['team2'] == $coachTeam,
            "scheidsrechter" => $wedstrijd['scheidsrechter'] ?? null,
            "isScheidsrechter" => ($wedstrijd['scheidsrechterId'] ?? null) == $userId,
            "tellers" => GetShortTeam(($wedstrijd['tellers'] ?? null)),
            "isTellers" => ($wedstrijd['tellers'] ?? null) == $team,
            "locatie" => $skcWedstrijd['locatie'],
        ];
    }
}
