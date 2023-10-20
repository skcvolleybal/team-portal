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

        $WedstrijdenOpDag = $this->GetWedstrijdenOpDag($data->datum, $uscWedstrijden);

        $TelFluitOpDag = $this->GetScheidsrechtersEnTellersOpDag($data->datum, $WedstrijdenOpDag);
        
        $BardienstenEnBHVOpDag = $this->GetBardienstenOpDag($data->datum);

        $ZaalwachtenOpDag = $this->GetZaalWachtenOpDag($data->datum);

        foreach($WedstrijdenOpDag as $index => $wedstrijd) {
            $wedstrijd->tellers = $TelFluitOpDag[$index]->tellers;
            $wedstrijd->scheidsrechter = $TelFluitOpDag[$index]->scheidsrechter;
        }

        $excelExport = new ExcelExport(
            $uscWedstrijden,
            $WedstrijdenOpDag,
            $TelFluitOpDag,
            $BardienstenEnBHVOpDag,
            $ZaalwachtenOpDag

        );
        $excelExport->GetExcelExport();
        $excelExport->returnExcelExport();

    }

    private function GetWedstrijdenVoorWeek() {
        $uscWedstrijden = $this->nevoboGateway->GetProgrammaForSporthal();
    }

    private function GetWedstrijdenOpDag($datum, $wedstrijden) {
        foreach($wedstrijden as $wedstrijd) {
            if ($wedstrijd->timestamp !== null) {
                if (preg_match('/' . $datum . '/', $wedstrijd->timestamp->format('Y-m-d H:i:s')) && 
                preg_match('/' . $wedstrijd->locatie . '/', "Universitair SC, Einsteinweg 6, 2333CC Leiden")) {
                    
                    $WedstrijdenOpDag[] = $wedstrijd;
                }
            }
        }
        return $WedstrijdenOpDag;
    }

    private function GetScheidsrechtersEnTellersOpDag($datum, $WedstrijdenOpDag) {
        $telfluit = array();
        foreach ($WedstrijdenOpDag as $wedstrijd) {
            $telfluit[] = $this->TelFluitGateway->GetWedstrijd($wedstrijd->matchId);
        }
        return $telfluit;
    }

    private function GetBardienstenOpDag($datum) {
        $dateTime = DateTime::createFromFormat("Y-m-d", $datum); //$datum is een string en GetBarDag verwacht een DateTime object
        $BarDagenOpDag = $this->BarcieGateway->GetBardag($dateTime);
        return $BarDagenOpDag;
    }

    private function GetZaalWachtenOpDag($datum) {
        $dateTime = DateTime::createFromFormat("Y-m-d", $datum); //$datum is een string en GetZaalWacht verwacht een DateTime object
        $ZaalWachtenOpDag = $this->ZaalwachtGateway->GetZaalwacht($dateTime);
        return $ZaalWachtenOpDag;
    }


}