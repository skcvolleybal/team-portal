<?php

namespace TeamPortal\UseCases;

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Bardienst;
use TeamPortal\Entities\Persoon;
use TeamPortal\Entities\Speeltijd;
use TeamPortal\Entities\Wedstrijd;
use TeamPortal\Entities\Wedstrijddag;
use TeamPortal\Entities\Zaalwacht;
use TeamPortal\Gateways;

error_reporting(E_ALL ^ E_DEPRECATED); // Suppress warnings on PHP 8.0. Make sure to fix the usort() functions in this file for PHP 8.1. 

class MijnTeamtaken implements Interactor
{
    public function __construct(
        Gateways\WordPressGateway $wordPressGateway,
        Gateways\NevoboGateway $nevoboGateway,
        Gateways\TelFluitGateway $telFluitGateway,
        Gateways\ZaalwachtGateway $zaalwachtGateway,
        Gateways\BarcieGateway $barcieGateway
    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->telFluitGateway = $telFluitGateway;
        $this->zaalwachtGateway = $zaalwachtGateway;
        $this->barcieGateway = $barcieGateway;
    }

    public function Execute(object $data = null)
    {
        $teamtaken = [];

        $bardiensten = [];
        $telbeurten = [];
        $bhvbeurten = [];
        $fluitbeurten = [];


        $user = $this->wordPressGateway->GetUser();

        // Wedstrijden where the user has refereed or counted 

        $wedstrijden = $this->telFluitGateway->GetFluitEnTelbeurtenFor($user, true);
        $wedstrijden = json_encode($wedstrijden);
        $wedstrijden = json_decode($wedstrijden, true);

        // Bardiensten where the user has BHV'd or gebardienst
        $bardiensten = $this->barcieGateway->GetBardienstenForUser($user, true);
        $bardiensten = json_encode($bardiensten);
        $bardiensten = json_decode($bardiensten, true);
        

        // Remap the bardienst and BHV data
        $remappedBardienstBHVData = array_map(function($item) {
            return [
                'type' => ($item['isBhv'] ? 'BHV' : 'Bardienst'),
                'date' => $item['bardag']['date']['date'],
                'shift'=> $item['shift']
                ];
        }, $bardiensten);

        $remappedFluitEnTelbeurten = array_map(function($item) {
            return [
                'type' => 'Telbeurt / Scheidsbeurt',
                'date' => $item['timestamp']['date']

            ];
        }, $wedstrijden);

        $combinedTeamTaken = array_merge($remappedBardienstBHVData, $remappedFluitEnTelbeurten);
        

        return $combinedTeamTaken;

    }


}
