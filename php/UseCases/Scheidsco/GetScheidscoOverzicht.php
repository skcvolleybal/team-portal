<?php

namespace TeamPortal\UseCases;

use TeamPortal\Gateways;

class GetScheidscoOverzicht implements Interactor
{
    public function __construct(
        Gateways\JoomlaGateway $joomlaGateway,
        Gateways\TelFluitGateway $telFluitGateway,
        Gateways\NevoboGateway $NevoboGateway,
        Gateways\ZaalwachtGateway $zaalwachtGateway
    ) {
        $this->joomlaGateway = $joomlaGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->nevoboGateway = $NevoboGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
    }

    public function Execute(object $data = null): array
    {
        $result = [];
        $wedstrijddagen = $this->nevoboGateway->GetWedstrijddagenForSporthal('LDNUN', 365);

        foreach ($wedstrijddagen as $wedstrijddag) {
            $wedstrijddag->zaalwacht = $this->zaalwachtGateway->GetZaalwacht($wedstrijddag->date);
            foreach ($wedstrijddag->speeltijden as $speeltijd) {
                foreach ($speeltijd->wedstrijden as &$wedstrijd) {
                    $indeling = $this->telFluitGateway->GetWedstrijd($wedstrijd->matchId);
                    if ($indeling !== null) {
                        $wedstrijd->scheidsrechter = $indeling->scheidsrechter;
                        $wedstrijd->telteam = $indeling->telteam;
                    }
                }
            }

            $result[] = new WedstrijddagModel($wedstrijddag);
        }

        return $result;
    }
}
