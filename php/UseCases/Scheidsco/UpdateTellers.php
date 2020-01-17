<?php

class UpdateTellers implements Interactor
{
    public function __construct(TelFluitGateway $telFluitGateway, JoomlaGateway $joomlaGateway)
    {
        $this->telFluitGateway = $telFluitGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data)
    {
        if ($data->matchId === null) {
            throw new InvalidArgumentException("matchId is null");
        }

        $wedstrijd = $this->telFluitGateway->GetWedstrijd($data->matchId);
        $wedstrijd->telteam = $this->joomlaGateway->GetTeamByNaam($data->tellers);
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
