<?php

class GenerateTeamstanden implements Interactor
{

    public function __construct(NevoboGateway $nevoboGateway)
    {
        $this->nevoboGateway = $nevoboGateway;
    }

    public function Execute()
    {
        $teams = Team::GetAlleSkcTeams();
        foreach ($teams as &$team) {
            $rankings = $this->nevoboGateway->GetStandForPoule($team->poule);

            $team->numberOfTeams = count($rankings);
            foreach ($rankings as $ranking) {
                if (strpos($ranking->team, $team->naam) !== false) {
                    $team->stand = $ranking->nummer;
                }
            }
        }

        if (count($teams) > 0) {
            $filename = dirname(__FILE__) . '/../Teamstanden/teamstanden.json';
            file_put_contents($filename, json_encode($teams));
        }

        return (object) [
            "teamstanden" => $teams,
        ];
    }
}
