<?php

class UpdateTellers implements IInteractorWithData
{
    public function __construct(TelFluitGateway $telFluitGateway, JoomlaGateway $joomlaGateway)
    {
        $this->telFluitGateway = $telFluitGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute($data)
    {
        $userId = $this->joomlaGateway->GetUserId();
        if ($userId === null) {
            throw new UnauthorizedException();
        }

        if (!$this->joomlaGateway->IsTeamcoordinator($userId)) {
            throw new UnexpectedValueException("Je bent (helaas) geen teamcoordinator");
        }

        $matchId = $data->matchId ?? null;
        $tellers = $data->tellers ?? null;

        if ($matchId === null) {
            throw new InvalidArgumentException("matchId is null");
        }

        $wedstrijd = $this->telFluitGateway->GetWedstrijd($matchId) ?? new Wedstrijd($matchId);
        $wedstrijd->telteam = $this->joomlaGateway->GetTeamByNaam($tellers) ?? null;
        if ($wedstrijd->id === null) {
            $this->telFluitGateway->Insert($wedstrijd);
        } else {
            if ($wedstrijd->telteam === null) {
                if ($wedstrijd->scheidsrechter === null) {
                    $this->telFluitGateway->Delete($wedstrijd);
                } else {
                    $this->telFluitGateway->Update($wedstrijd);
                }
            } else {
                $this->telFluitGateway->Update($wedstrijd);
            }
        }
    }
}
