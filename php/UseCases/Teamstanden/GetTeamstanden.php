<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\Utilities;

class GetTeamstanden implements Interactor
{
    public function Execute(object $data = null)
    {
        $teams = json_decode(file_get_contents(dirname(__FILE__) . "/../../Teamstanden/teamstanden.json"), false);
        $numberOfSkcTeams = count($teams);

        $teamnames = [];
        $bordercolors = [];
        $backgroundcolor = [];
        foreach ($teams as $i => $team) {
            $teamnames[] = '"' . $team->skcNaam . '"';
            if ($i % 2 == 0) {
                $backgroundcolor[] = "'rgba(75, 192, 192, 0.2)'";
                $bordercolors[] = "'rgba(75, 192, 192, 1)'";
            } else {
                $backgroundcolor[] = "'rgba(75, 192, 192, 0.2)'";
                $bordercolors[] = "'rgba(54, 162, 235, 1)'";
            }
        }

        $rankings = array();
        $numberOfTeamsInPoule = array();
        $maxNumberOfTeams = 0;
        foreach ($teams as $team) {
            $numberOfTeamsInPoule[] = $team->numberOfTeamsInPoule;
            $rankings[] = $team->numberOfTeamsInPoule - $team->positie + 1;
            $actualRanks[] = $team->positie;
            if ($maxNumberOfTeams < $team->numberOfTeamsInPoule) {
                $maxNumberOfTeams = $team->numberOfTeamsInPoule;
            }
        }

        $blackBars = array();
        foreach ($teams as $team) {
            $blackBars[] = $maxNumberOfTeams - $team->numberOfTeamsInPoule;
        }

        $template = file_get_contents("./UseCases/Teamstanden/templates/teamstanden.html");
        $placeholders = [
            "__ACTUAL_RANKS__" => implode(", ", $actualRanks),
            "__NUMBER_OF_TEAMS_IN_POULE__" => implode(", ", $numberOfTeamsInPoule),
            "__ALL_TEAMS__" => implode(", ", $teamnames),
            "__BLACKBARS__" => implode(", ", $blackBars),
            "__RANKINGS__" => implode(", ", $rankings),
            "__BACKGROUNDCOLOR__" => implode(", ", array_fill(0, $numberOfSkcTeams, "'rgba(0, 0, 0, 0.05)'")),
            "__BORDERCOLOR__" => implode(", ", array_fill(0, $numberOfSkcTeams, "'rgba(0, 0, 0, 0.5)'")),
            "__DATABACKGROUNDCOLOR__" => implode(", ", $backgroundcolor),
            "__DATABORDERCOLOR__" => implode(", ", $bordercolors),
        ];

        $body = Utilities::FillTemplate($template, $placeholders);
        echo $body;
    }
}
