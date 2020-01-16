<?php

class UpdateTellers implements IInteractorWithData
{
    public function __construct(TelFluitGateway $telFluitGateway, JoomlaGateway $joomlaGateway)
    {
        $this->telFluitGateway = $telFluitGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data)
    {
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
