<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Wedstrijd;
use TeamPortal\Gateways;

class GetVoorpaginaRooster implements Interactor
{
    public function __construct(
        Gateways\TelFluitGateway $telFluitGateway,
        Gateways\NevoboGateway $nevoboGateway,
        Gateways\ZaalwachtGateway $zaalwachtGateway
    ) {
        $this->telFluitGateway = $telFluitGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
    }

    public function Execute(object $data = null)
    {
        $telEnFluitWedstrijden = $this->telFluitGateway->GetAllFluitEnTelbeurten();

        $result = [];
        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal();
        foreach ($wedstrijddagen as $wedstrijddag) {
            $wedstrijddag->zaalwacht = $this->zaalwachtGateway->GetZaalwacht($wedstrijddag->date);
            foreach ($wedstrijddag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as $wedstrijd) {
                    $telEnFluitWedstrijd = Wedstrijd::GetWedstrijdWithMatchId($telEnFluitWedstrijden, $wedstrijd->matchId);
                    $wedstrijd->AppendInformation($telEnFluitWedstrijd);
                }
            }
            $result[] = new WedstrijddagModel($wedstrijddag);
        }

        return $result;
    }
}
