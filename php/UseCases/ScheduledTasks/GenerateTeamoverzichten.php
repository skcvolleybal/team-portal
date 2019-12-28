<?php

class GenerateTeamoverzichten implements IInteractor
{

    public function __construct($database)
    {
        $this->nevoboGateway = new NevoboGateway();
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute()
    {
        $teams = GetAllSkcTeams();
        $result = [];
        foreach ($teams as $team) {
            $ranking = $this->nevoboGateway->GetStandForPoule($team->poule);
            $uitslagen = $this->nevoboGateway->GetUitslagenForTeam($team->naam);
            $programma = $this->nevoboGateway->GetProgrammaForTeam($team->naam);

            $result[] = (object) [
                "naam" => $team->naam,
                "poule" => $team->poule,
                "trainer" => $this->joomlaGateway->GetTrainers($team->naam),
                "trainingstijden" => $team->trainingstijden,
                "coaches" => $this->joomlaGateway->GetCoaches($team->naam),
                "facebook" => $team->facebook ?? null,
                "stand" => $ranking,
                "uitslagen" => array_slice($uitslagen, 0, 3),
                "programma" => array_slice($programma, 0, 3),
            ];
        }

        if (count($result) > 0) {
            $filename = dirname(__FILE__) . "/../Teamstanden/teamoverzichten.json";
            file_put_contents($filename, json_encode($result));
        }

        return (object) [
            "teamoverzichten" => $result,
        ];
    }
}
