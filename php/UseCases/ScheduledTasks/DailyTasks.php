<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Team;
use TeamPortal\Gateways\JoomlaGateway;
use TeamPortal\Gateways\NevoboGateway;

class DailyTasks implements Interactor
{
    public function __construct(
        NevoboGateway $nevoboGateway,
        JoomlaGateway $joomlaGateway
    ) {
        $this->nevoboGateway = $nevoboGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $result = [];
        $result[] = $this->GenerateTeamstanden();
        $result[] = $this->GenerateTeamoverzichten();
        print_r($result);
    }

    private function GenerateTeamoverzichten()
    {
        $result = [];
        $teams = Team::$alleSkcTeams;
        foreach ($teams as $team) {
            $uitslagen = $this->nevoboGateway->GetUitslagenForTeam($team);
            $programma = $this->nevoboGateway->GetWedstrijdenForTeam($team);

            $team->stand = $this->nevoboGateway->GetStandForPoule($team->poule);
            $team->uitslagen = array_slice($uitslagen, 0, 3);
            $team->programma = array_slice($programma, 0, 3);
            $team->trainers = $this->joomlaGateway->GetTrainers($team);
            $team->coaches = $this->joomlaGateway->GetCoaches($team);
            
            $dirname = dirname(__FILE__) . "/../../Teamstanden";
            if (!file_exists($dirname)) {
                mkdir($dirname);
            }

            $filename = $dirname . "/" . str_replace(" ", "", $team->GetSkcNaam()) . ".json";
            file_put_contents($filename, json_encode(new TeamoverzichtModel($team)));
        }

        return $result;
    }

    private function GenerateTeamstanden()
    {
        $teams = Team::$alleSkcTeams;
        foreach ($teams as $team) {
            $rankings = $this->nevoboGateway->GetStandForPoule($team->poule);
            $team->skcNaam = $team->GetSkcNaam();
            $team->numberOfTeamsInPoule = count($rankings);
            foreach ($rankings as $ranking) {
                if ($ranking->team->naam === $team->naam) {
                    $team->positie = $ranking->nummer;
                }
            }
        }

        if (count($teams) > 0) {
            $dirname = dirname(__FILE__) . '/../../Teamstanden';
            if (!file_exists($dirname)) {
                mkdir($dirname);
            }

            file_put_contents($dirname . "/teamstanden.json", json_encode($teams));
        }

        return $teams;
    }
}
