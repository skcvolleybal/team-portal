<?php
class FluitBeschikbaarheidHelper
{
    public function GetWedstrijdWithDate($programma, $date)
    {
        foreach ($programma as $wedstrijd) {
            if ($wedstrijd->timestamp && $wedstrijd->timestamp->format("Y-m-d") == $date) {
                return $wedstrijd;
            }
        }
        return null;
    }

    public function isMogelijk($wedstrijden, $tijd)
    {
        $bestResult = "Ja";
        foreach ($wedstrijden as $wedstrijd) {
            $format = 'Y-m-d H:i:s';
            $timestring = $wedstrijd->timestamp->format('Y-m-d') . " " . $tijd;
            $fluitWedstrijd = (object) [
                "timestamp" => DateTime::createFromFormat($format, $timestring),
                "locatie" => "Universitair SC",
            ];
            $isMogelijk = isMogelijk($wedstrijd, $fluitWedstrijd);
            if ($isMogelijk === "Nee") {
                return "Nee";
            }
            $bestResult = $isMogelijk === "Onbekend" ? "Onbekend" : $bestResult;
        }

        return $bestResult;
    }

    public function GetFluitBeschikbaarheid($fluitBeschikbaarheden, $date, $time)
    {
        foreach ($fluitBeschikbaarheden as $fluitBeschikbaarheid) {
            if ($fluitBeschikbaarheid->date == $date && $fluitBeschikbaarheid->time == $time) {
                return $fluitBeschikbaarheid->is_beschikbaar;
            }
        }
        return null;
    }

    public function GetUscRooster($skcProgramma, $team, $coachTeam)
    {
        $rooster = [];
        foreach ($skcProgramma as $wedstrijd) {
            $datum = GetDutchDate($wedstrijd->timestamp);
            $date = $wedstrijd->timestamp->format("Y-m-d");
            $tijd = $wedstrijd->timestamp->format("G:i");
            $time = $wedstrijd->timestamp->format("G:i:s");
            $team1 = $wedstrijd->team1;
            $team2 = $wedstrijd->team2;

            $i = $this->GetIndexOfDatum($rooster, $datum);
            if ($i === null) {
                $rooster[] = (object) [
                    "datum" => $datum,
                    "date" => $date,
                    "speeltijden" => [],
                ];
                $i = count($rooster) - 1;
            }
            $j = $this->GetIndexOfTijd($rooster[$i]->speeltijden, $time);
            if ($j === null) {
                $rooster[$i]->speeltijden[] = (object) [
                    "tijd" => $tijd,
                    "time" => $time,
                    "wedstrijden" => [],
                ];
                $j = count($rooster[$i]->speeltijden) - 1;
            }

            $rooster[$i]->speeltijden[$j]->wedstrijden[] = (object) [
                "team1" => $team1,
                "isTeam1" => $team == $team1,
                "isCoachTeam1" => $coachTeam == $team1,
                "team2" => $team2,
                "isTeam2" => $team == $team2,
                "isCoachTeam2" => $coachTeam == $team2,
            ];
        }

        return $rooster;
    }

    public function GetIndexOfDatum($rooster, $datum)
    {
        for ($i = count($rooster) - 1; $i >= 0; $i--) {
            if ($rooster[$i]->datum == $datum) {
                return $i;
            }
        }
        return null;
    }

    public function GetIndexOfTijd($roosterDag, $time)
    {
        for ($i = count($roosterDag) - 1; $i >= 0; $i--) {
            if ($roosterDag[$i]->time == $time) {
                return $i;
            }
        }
        return null;
    }
}
