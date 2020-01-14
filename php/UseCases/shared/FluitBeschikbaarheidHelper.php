<?php
// class FluitBeschikbaarheidHelper
// {





//     // public function GetUscRooster($skcProgramma, Team $team, Team $coachTeam): array
//     // {
//     //     $rooster = [];
//     //     foreach ($skcProgramma as $wedstrijd) {
//     //         $date = $wedstrijd->timestamp->format("Y-m-d");
//     //         $time = $wedstrijd->timestamp->format("G:i");

//     //         $i = $this->GetIndexOfDatum($rooster, $wedstrijd->timestamp);
//     //         if ($i === null) {
//     //             $rooster[] = (object) [
//     //                 "datum" => DateFunctions::GetDutchDate($wedstrijd->timestamp),
//     //                 "date" => $date,
//     //                 "speeltijden" => [],
//     //             ];
//     //             $i = count($rooster) - 1;
//     //         }
//     //         $j = $this->GetIndexOfTijd($rooster[$i]->speeltijden, $time);
//     //         if ($j === null) {
//     //             $rooster[$i]->speeltijden[] = (object) [
//     //                 "time" => $time,
//     //                 "wedstrijden" => [],
//     //             ];
//     //             $j = count($rooster[$i]->speeltijden) - 1;
//     //         }

//     //         $rooster[$i]->speeltijden[$j]->wedstrijden[] = (object) [
//     //             "team1" => $wedstrijd->team1->naam,
//     //             "isTeam1" => $wedstrijd->team1->Equals($team),
//     //             "isCoachTeam1" => $wedstrijd->team1->Equals($coachTeam),
//     //             "team2" => $wedstrijd->team2->naam,
//     //             "isTeam2" => $wedstrijd->team2->Equals($team),
//     //             "isCoachTeam2" => $wedstrijd->team2->Equals($coachTeam),
//     //         ];
//     //     }

//     //     return $rooster;
//     // }

//     // public function GetIndexOfDatum(array $rooster, DateTime $date): ?int
//     // {
//     //     for ($i = count($rooster) - 1; $i >= 0; $i--) {
//     //         if ($rooster[$i]->date === DateFunctions::GetYmdNotation($date)) {
//     //             return $i;
//     //         }
//     //     }
//     //     return null;
//     // }

//     // public function GetIndexOfTijd($roosterDag, $time): ?int
//     // {
//     //     for ($i = count($roosterDag) - 1; $i >= 0; $i--) {
//     //         if ($roosterDag[$i]->time == $time) {
//     //             return $i;
//     //         }
//     //     }
//     //     return null;
//     // }
// }
