<?php

class UpdateTellers implements IInteractorWithData
{
    public function __construct($database)
    {
        $this->telFluitGateway = new TelFluitGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            UnauthorizedResult();
        }

        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent (helaas) geen teamcoordinator");
        }

        $matchId = $data->matchId ?? null;
        $tellers = $data->tellers ?? null;

        if ($matchId == null) {
            throw new InvalidArgumentException("matchId is null");
        }

        $team = $this->joomlaGateway->GetTeamByNaam($tellers);
        $wedstrijd = $this->telFluitGateway->GetWedstrijd($matchId);
        if ($wedstrijd == null) {
            if ($team) {
                $this->telFluitGateway->Insert($matchId, null, $team->id);
            }
        } else {
            if ($team == null) {
                if ($wedstrijd->scheidsrechterId == null) {
                    $this->telFluitGateway->Delete($matchId);
                } else {
                    $this->telFluitGateway->Update($matchId, $wedstrijd->scheidsrechterId, null);
                }
            } else {
                $this->telFluitGateway->Update($matchId, $wedstrijd->scheidsrechterId, $team->id);
            }
        }

        exit();
    }

}
