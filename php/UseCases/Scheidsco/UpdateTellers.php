<?php

class UpdateTellers implements Interactor
{
    public function __construct(
        TelFluitGateway $telFluitGateway, 
        JoomlaGateway $joomlaGateway,
        NevoboGateway $nevoboGateway)
    {
        $this->telFluitGateway = $telFluitGateway;
        $this->joomlaGateway = $joomlaGateway;
        $this->nevoboGateway = $nevoboGateway;
    }

    public function Execute(object $data = null)
    {
        if ($data->matchId === null) {
            throw new InvalidArgumentException("matchId is null");
        }

        $wedstrijd = $this->telFluitGateway->GetWedstrijd($data->matchId);
        $wedstrijd->telteam = $this->joomlaGateway->GetTeamByNaam($data->tellers);

        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
        $uscWedstrijd = Wedstrijd::GetWedstrijdWithMatchId($uscWedstrijden, $data->matchId);
        $wedstrijd->AppendInformation($uscWedstrijd);

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
