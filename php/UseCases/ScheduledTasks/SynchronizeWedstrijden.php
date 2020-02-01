<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Wedstrijd;
use TeamPortal\Gateways;

class SynchronizeWedstrijden implements Interactor
{
    public function __construct(
        Gateways\Database $database,
        Gateways\TelFluitGateway $telFluitGateway,
        Gateways\NevoboGateway $nevoboWedstrijd
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
