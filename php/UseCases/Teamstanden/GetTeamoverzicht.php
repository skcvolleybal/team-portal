<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Team;
use TeamPortal\Gateways;
use UnexpectedValueException;

class GetTeamoverzicht implements Interactor
{
    public function __construct(
        Gateways\JoomlaGateway $joomlaGateway,
        Gateways\NevoboGateway $nevoboGateway
    ) {
        $this->nevoboGateway = $nevoboGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        if ($data->teamnaam === null) {
            throw new InvalidArgumentException("Teamnaam is leeg");
        }

        $teamnaam = str_replace("-", " ", $data->teamnaam);
        $currentTeam = new Team($teamnaam);
        $teams = Team::$alleSkcTeams;
        foreach ($teams as $team) {
            if ($currentTeam->naam !== $team->naam) {
                continue;
            }

            $uitslagen = $this->nevoboGateway->GetUitslagenForTeam($team);
            $programma = $this->nevoboGateway->GetWedstrijdenForTeam($team);

            $team->coaches = $this->joomlaGateway->GetCoaches($team);
            $team->trainers = $this->joomlaGateway->GetTrainers($team);
            $team->stand = $this->nevoboGateway->GetStandForPoule($team->poule);
            $team->uitslagen = array_slice($uitslagen, 0, 3);
            $team->programma = array_slice($programma, 0, 3);

            return new TeamoverzichtModel($team);
        }

        throw new UnexpectedValueException("Team met naam '$teamnaam' bestaat niet");
    }
}
