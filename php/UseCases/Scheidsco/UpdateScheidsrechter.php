<?php

class UpdateScheidsrechter implements Interactor
{
    public function __construct(
        TelFluitGateway $telFluitGateway,
        JoomlaGateway $joomlaGateway
    ) {
        $this->telFluitGateway = $telFluitGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data)
    {
        if ($data->matchId === null) {
            throw new InvalidArgumentException("matchId is null");
        }

        $wedstrijd = $this->telFluitGateway->GetWedstrijd($data->matchId);
        $wedstrijd->scheidsrechter = $this->joomlaGateway->GetScheidsrechter($data->scheidsrechterId);

        if ($wedstrijd->id === null) {
            $this->telFluitGateway->Insert($wedstrijd);
        } else {
            if ($wedstrijd->scheidsrechter === null) {
                if ($wedstrijd->telteam === null) {
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
