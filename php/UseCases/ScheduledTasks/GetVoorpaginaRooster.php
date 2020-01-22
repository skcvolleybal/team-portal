<?php

class GetVoorpaginaRooster implements Interactor
{
    public function __construct(
        TelFluitGateway $telFluitGateway,
        NevoboGateway $nevoboGateway,
        ZaalwachtGateway $zaalwachtGateway
    ) {
        $this->telFluitGateway = $telFluitGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
    }

    public function Execute(object $data = null)
    {
        $this->telFluitBeurten = $this->telFluitGateway->GetAllFluitEnTelbeurten();
        $this->zaalwachten = $this->zaalwachtGateway->GetZaalwachtIndeling();

        $result = [];
        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal();
        foreach ($wedstrijddagen as $wedstrijddag) {
            $dag = new WedstrijddagModel($wedstrijddag);
            foreach ($dag->speeltijden as $speeltijd){
                foreach ($speeltijd->wedstrijden as $wedstrijd){
                    
                }
            }
            $result[] = $dag;
        }

        return $result;
    }
}
