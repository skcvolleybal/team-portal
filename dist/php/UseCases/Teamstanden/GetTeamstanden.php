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

        exit($template);
    }
}
