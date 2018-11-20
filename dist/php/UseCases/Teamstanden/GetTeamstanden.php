<?php
include_once 'IInteractor.php';

class GetTeamstanden implements IInteractor
{

    public function __construct()
    {

    }

    public function Execute()
    {
        $teams = json_decode(file_get_contents(dirname(__FILE__) . "/teamstanden.json"), true);
        $numberOfSkcTeams = count($teams);

        $teamnames = [];
        foreach ($teams as $team) {
            $teamnames[] = '"' . GetShortTeam($team['naam']) . '"';
        }

        $rankings = array();
        $numberOfTeams = array();
        $rankingInLength = array();
        $maxNumberOfTeams = 0;
        foreach ($teams as $team) {
            $numberOfTeams[] = $team["numberOfTeams"];
            $rankings[] = $team["numberOfTeams"] - $team["stand"] + 1;
            $actualRanks[] = $team["stand"];
            if ($maxNumberOfTeams < $team["numberOfTeams"]) {
                $maxNumberOfTeams = $team["numberOfTeams"];
            }
        }

        $blackBars = array();
        foreach ($teams as $team) {
            $blackBars[] = $maxNumberOfTeams - $team["numberOfTeams"];
        }

        $template = file_get_contents("./UseCases/Teamstanden/templates/teamstanden.html");
        $template = str_replace("__ACTUAL_RANKS__", implode(", ", $actualRanks), $template);
        $template = str_replace("__NUMBER_OF_TEAMS__", implode(", ", $numberOfTeams), $template);
        $template = str_replace("__BLACKBARS__", implode(", ", $blackBars), $template);
        $template = str_replace("__RANKINGS__", implode(", ", $rankings), $template);
        $template = str_replace("__ALLTEAMS__", implode(", ", $teamnames), $template);
        $template = str_replace("__BACKGROUNDCOLOR__", implode(", ", array_fill(0, $numberOfSkcTeams, "'rgba(0, 0, 0, 0.05)'")), $template);
        $template = str_replace("__BORDERCOLOR__", implode(", ", array_fill(0, $numberOfSkcTeams, "'rgba(0, 0, 0, 0.5)'")), $template);
        $template = str_replace("__DATABACKGROUNDCOLOR__", implode(", ", array_fill(0, $numberOfSkcTeams, "'rgba(75, 192, 192, 0.2)'")), $template);
        $template = str_replace("__DATABORDERCOLOR__", implode(", ", array_fill(0, $numberOfSkcTeams, "'rgba(75, 192, 192, 1)'")), $template);

        exit($template);
    }
}
