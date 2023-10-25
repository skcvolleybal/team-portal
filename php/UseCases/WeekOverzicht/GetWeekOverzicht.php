<?php

namespace TeamPortal\UseCases;

require_once('ExcelExport.php'); // Had het eerst in de dir UseCases\ExcelExport maar dat pad werkt niet dus dan maar in deze dir

use TeamPortal\Common\DateFunctions;
use TeamPortal\Entities\Coach;
use TeamPortal\Entities\Invaller;
use TeamPortal\Entities\Persoon;
use TeamPortal\Gateways;
use TeamPortal\Entities\Speler;
use TeamPortal\Entities\Team;
use TeamPortal\Entities\Wedstrijd;

use UnexpectedValueException;

use datetime;

error_reporting(E_ALL ^ E_DEPRECATED); // Suppress warnings on PHP 8.0. Make sure to fix the usort() functions in this file for PHP 8.1. 

// Niet heel lekker over dit verhaal nagedacht maar he je leert er wat van
class GetWeekOverzicht implements Interactor
{
    public function __construct(
        Gateways\WordPressGateway $wordPressGateway,
        Gateways\AanwezigheidGateway $aanwezigheidGateway,
        Gateways\NevoboGateway $nevoboGateway,
        Gateways\TelFluitGateway $TelFluitGateway,
        GateWays\BarcieGateway $BarcieGateway,
        GateWays\ZaalwachtGateway $ZaalwachtGateway,

    ) {
        $this->wordPressGateway = $wordPressGateway;
        $this->aanwezigheidGateway = $aanwezigheidGateway;
        $this->nevoboGateway = $nevoboGateway;
        $this->TelFluitGateway = $TelFluitGateway;
        $this->BarcieGateway = $BarcieGateway;
        $this->ZaalwachtGateway = $ZaalwachtGateway;
    }

    public function Execute(object $data = null)
    {
        $WedstrijdenOpDag = array();
        if ($data->datum == "undefined" || $data == null) {
            throw new \ValueError(sprintf('Argument #1 (date) must be a valid date'));
        }
       
        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForVereniging();

        $allMatches = $this->GetWedstrijdenTotEnMetDag($data->datum, $uscWedstrijden);

        $allBardienstenEnBHV = $this->GetBardienstenTotEnMetDag($data->datum);

        $allZaalwachten = $this->GetZaalWachtenTotEnMetDag($data->datum);

        $excelExport = new ExcelExport(
            $allMatches,
            $allBardienstenEnBHV,
            $allZaalwachten,

            $this->BarcieGateway,
            $this->ZaalwachtGateway
        );
        $excelExport->GetExcelExport();
        $excelExport->returnExcelExport();

    }

    private function GetWedstrijdenOpDag($datum, $wedstrijden) {
        $WedstrijdenOpDag = array();
        foreach($wedstrijden as $wedstrijd) {
            if ($wedstrijd->timestamp !== null) {
                if (preg_match('/' . $datum . '/', $wedstrijd->timestamp->format('Y-m-d H:i:s')) && 
                preg_match('/' . $wedstrijd->locatie . '/', "Universitair SC, Einsteinweg 6, 2333CC Leiden")) {
                    
                    $WedstrijdenOpDag[] = $wedstrijd;
                }
            }
        }
        return $WedstrijdenOpDag ? $WedstrijdenOpDag : null;
    }

    private function GetScheidsrechtersEnTellersOpDag($wedstrijd) {
        $telfluit = array();
        $telfluit[] = $this->TelFluitGateway->GetWedstrijd($wedstrijd->matchId);
        return $telfluit ? $telfluit : null;
    }

    private function GetBardienstenOpDag($datum) {
        $dateTime = DateTime::createFromFormat("Y-m-d", $datum); //$datum is een string en GetBarDag verwacht een DateTime object
        $BarDagenOpDag = $this->BarcieGateway->GetBardag($dateTime);
        return $BarDagenOpDag->id != null ? $BarDagenOpDag : null;
    }

    private function GetZaalWachtenOpDag($datum) {
        $dateTime = DateTime::createFromFormat("Y-m-d", $datum); //$datum is een string en GetZaalWacht verwacht een DateTime object
        $ZaalWachtenOpDag = $this->ZaalwachtGateway->GetZaalwacht($dateTime);
        return $ZaalWachtenOpDag;
    }


    private function GetWedstrijdenTotEnMetDag($datum, $uscWedstrijden) {
        // datum
        $startDate = new DateTime();  // Initialize with the current date and time
        $endDate = new DateTime($datum);  // Replace '2023-12-31' with your desired end date
        $endDate = $endDate->modify('+1 day');
        $allGames = array();
        for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $temp = $this->GetWedstrijdenOpDag($date->format('Y-m-d'), $uscWedstrijden);
            if ($temp) { $allGames[] = $temp; }
        }

        // Zet scheidsrechters en tellers in de $allGames array
        foreach($allGames as $WedstrijdenOpDag) {
            foreach($WedstrijdenOpDag as $wedstrijd) {
                $telfluit = $this->GetScheidsrechtersEnTellersOpDag($wedstrijd);
                $wedstrijd->tellers = $telfluit[0]->tellers;
                $wedstrijd->scheidsrechter = $telfluit[0]->scheidsrechter;
            }
        }


        return $allGames;
    }

    private function GetBardienstenTotEnMetDag($datum) {
        $startDate = new DateTime();  // Initialize with the current date and time
        $endDate = new DateTime($datum);  // Replace '2023-12-31' with your desired end date
        $endDate = $endDate->modify('+1 day');
        $allBardiensten = array();
        for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $temp = $this->GetBardienstenOpDag($date->format('Y-m-d'));
            if ($temp) { $allBardiensten[] = $temp; }
        }
        return $allBardiensten;
    }

    private function GetZaalWachtenTotEnMetDag($datum) {
        $startDate = new DateTime();  // Initialize with the current date and time
        $endDate = new DateTime($datum);  // Replace '2023-12-31' with your desired end date
        $endDate = $endDate->modify('+1 day');
        $allZaalwachten = array();
        for ($date = clone $startDate; $date <= $endDate; $date->modify('+1 day')) {
            $temp = $this->GetZaalWachtenOpDag($date->format('Y-m-d'));
            if ($temp) { $allZaalwachten[] = $temp; }
        }
        return $allZaalwachten;

    }
}