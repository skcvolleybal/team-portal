<?php

class GetTeamstanden implements Interactor
{
    public function Execute()
    {
        $teams = json_decode(file_get_contents(dirname(__FILE__) . "/teamstanden.json"), false);
        $numberOfSkcTeams = count($teams);

        $teamnames = [];
        $bordercolors = [];
        $backgroundcolor = [];
        foreach ($teams as $i => $team) {
            $teamnames[] = '"' . GetShortTeam($team->naam) . '"';
            if ($i % 2 == 0) {
                $backgroundcolor[] = "'rgba(75, 192, 192, 0.2)'";
                $bordercolors[] = "'rgba(75, 192, 192, 1)'";
            } else {
                $backgroundcolor[] = "'rgba(75, 192, 192, 0.2)'";
                $bordercolors[] = "'rgba(54, 162, 235, 1)'";
            }
        }

        $rankings = array();
        $numberOfTeams = array();
        $maxNumberOfTeams = 0;
        foreach ($teams as $team) {
            $numberOfTeams[] = $team->numberOfTeams;
            $rankings[] = $team->numberOfTeams - $team->stand + 1;
            $actualRanks[] = $team->stand;
            if ($maxNumberOfTeams < $team->numberOfTeams) {
                $maxNumberOfTeams = $team->numberOfTeams;
            }
        }

        $blackBars = array();
        foreach ($teams as $team) {
            $blackBars[] = $maxNumberOfTeams - $team->numberOfTeams;
        }

        $template = file_get_contents("./UseCases/Teamstanden/templates/teamstanden.html");
        $template = str_replace("__ACTUAL_RANKS__", implode(", ", $actualRanks), $template);
        $template = str_replace("__NUMBER_OF_TEAMS__", implode(", ", $numberOfTeams), $template);
        $template = str_replace("__BLACKBARS__", implode(", ", $blackBars), $template);
        $template = str_replace("__RANKINGS__", implode(", ", $rankings), $template);
        $template = str_replace("__ALLTEAMS__", implode(", ", $teamnames), $template);
        $template = str_replace("__BACKGROUNDCOLOR__", implode(", ", array_fill(0, $numberOfSkcTeams, "'rgba(0, 0, 0, 0.05)'")), $template);
        $template = str_replace("__BORDERCOLOR__", implode(", ", array_fill(0, $numberOfSkcTeams, "'rgba(0, 0, 0, 0.5)'")), $template);
        $template = str_replace("__DATABACKGROUNDCOLOR__", implode(", ", $backgroundcolor), $template);
        $template = str_replace("__DATABORDERCOLOR__", implode(", ", $bordercolors), $template);

        return $template;
    }
}
