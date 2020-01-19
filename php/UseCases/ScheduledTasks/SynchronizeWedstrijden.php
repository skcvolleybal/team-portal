<?php

class SynchronizeWedstrijden implements Interactor
{
    public function __construct(
        Database $database,
        TelFluitGateway $telFluitGateway,
        NevoboGateway $nevoboWedstrijd
    ) {
        $this->database = $database;
        $this->telFluitGateway = $telFluitGateway;
        $this->nevoboGateway = $nevoboWedstrijd;
    }

    public function Execute(object $data = null): array
    {
        $result = [];
        $nevoboWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
        $dbWedstrijden = $this->telFluitGateway->GetAllFluitEnTelbeurten();

        foreach ($dbWedstrijden as $wedstrijd) {
            $nevoboWedstrijd = Wedstrijd::GetWedstrijdWithMatchId($nevoboWedstrijden, $wedstrijd->matchId);
            if ($nevoboWedstrijd === null) {
                continue;
            }

            if ($wedstrijd->timestamp === null || $wedstrijd->timestamp != $nevoboWedstrijd->timestamp) {
                $wedstrijd->timestamp = $nevoboWedstrijd->timestamp;
                $wedstrijd->isVeranderd = true;
                $this->telFluitGateway->Update($wedstrijd);
                $result[] = $wedstrijd;
            }
        }

        return $result;
    }
}
