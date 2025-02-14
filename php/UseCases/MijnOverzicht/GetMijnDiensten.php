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

class GetMijnDiensten implements Interactor
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
        $overzicht = [];

        $user = $this->wordPressGateway->GetUser();

        $zaalwachten = $this->zaalwachtGateway->GetZaalwachtenOfUser($user);
        foreach ($zaalwachten as $zaalwacht) {
            $this->AddZaalwachtToOverzicht($overzicht, $zaalwacht, $user);
        }

        $wedstrijden = $this->telFluitGateway->GetFluitEnTelbeurtenFor($user);

        $bardiensten = $this->barcieGateway->GetBardienstenForUser($user);
        foreach ($bardiensten as $bardienst) {
            $this->AddBardienstToOverzicht($overzicht, $bardienst);
        }

        // foreach ($overzicht as $dag) {
        //     usort($dag->speeltijden, [Speeltijd::class, "Compare"]);
        // }
        // When enabled, speeltijd 17:30 comes before 15:30... So disabled for now. 



        return $this->MapToUseCaseModel($overzicht, $user);
    }
}
