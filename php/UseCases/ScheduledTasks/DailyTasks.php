<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Team;
use TeamPortal\Gateways\NevoboGateway;

class DailyTasks implements Interactor
{
    public function __construct(NevoboGateway $nevoboGateway)
    {
        $this->nevoboGateway = $nevoboGateway;
    }

    public function Execute(object $data = null)
    {
        $result = $this->GenerateTeamstanden();
        print_r($result);
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
