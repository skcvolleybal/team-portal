<?php

class UpdateScheidsrechter implements IInteractorWithData
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
        $matchId = $data->matchId ?? null;
        $scheidsrechterId = $data->scheidsrechterId ?? null;

        if ($matchId === null) {
            throw new InvalidArgumentException("matchId is null");
        }

        $wedstrijd = $this->telFluitGateway->GetWedstrijd($matchId) ?? new Wedstrijd($matchId);
        $wedstrijd->scheidsrechter = $scheidsrechterId ? $this->joomlaGateway->GetScheidsrechter($scheidsrechterId) : null;

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
