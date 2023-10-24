<?php

namespace TeamPortal\UseCases;
namespace TeamPortal\Entities\Scheidsrechter;

require 'vendor/autoload.php'; // Include Composer's autoloader



use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use TeamPortal\Gateways;

use datetime;



class ExcelExport
{

    public function __construct(
        $allMatches,
        $allBardienstenEnBHV,
        $allZaalwachten,

        $BarcieGateway,
        $ZaalwachtGateway
    ) 
    
    {
        $this->allMatches = $allMatches;
        $this->allBardienstenEnBHV = $allBardienstenEnBHV;
        $this->allZaalwachten = $allZaalwachten;

        $this->BarcieGateway = $BarcieGateway;
        $this->ZaalwachtGateway = $ZaalwachtGateway;
    }

    private $tweedeZaalwachtRow; // Wordt gebruikt om bar en bhv dienst mooi neer te zetten, ingevuld in CreateZaalwachtSchema
    private $Spreadsheet;

    private $allMatches;
    private $allBardienstenEnBHV;
    private $allZaalwachten;

    private $WedstrijdenOpDag;
    private $BardienstenEnBHVOpDag;
    private $ZaalwachtenOpDag;

    private $currentRow;
    private $veldNummer;
    private $kleurNummer;

    public function GetExcelExport() {
        // Init the spreadsheet
        $this->currentRow = 1;
        $this->Spreadsheet = new Spreadsheet();
        $this->Spreadsheet->setActiveSheetIndex(0);


        foreach($this->allMatches as $WedstrijdenOpDag) {
            $this->WedstrijdenOpDag = $WedstrijdenOpDag;
            $this->BardienstenEnBHVOpDag = $this->GetBardienstenEnBHVOpDag();
            $this->ZaalwachtenOpDag = $this->GetZaalwachtenOpDag();
            $this->currentRow += 1;
            $this->CreateFirstRow();
            $this->currentRow += 1;

            $this->CreateZaalwachtSchema($this->currentRow);
            $this->currentRow += 1;

            $this->CreateWedstrijdSchema($this->currentRow);

            $this->CreateBarEnBHVSchema($this->currentRow);
            $this->currentRow += 2; // Leave some extra room for the board availability

        }

    }

    // Creates the first row that is always the same: has Wedstrijd, Tijd, Team A, Team B, Niveau, Veldnummer, Scheidrechter en Tellers als text
    private function CreateFirstRow() {
        $this->SetCell('A'. $this->currentRow, 'Wedstrijd');
        $this->SetCell('B'. $this->currentRow , 'Tijd');
        $this->SetCell('C'. $this->currentRow, 'Team A');
        $this->SetCell('D'. $this->currentRow, 'Team B');
        $this->SetCell('E'. $this->currentRow, 'Niveau');
        $this->SetCell('F'. $this->currentRow, 'Veldnummer');
        $this->SetCell('G'. $this->currentRow, 'Scheidsrechter');
        $this->SetCell('H'. $this->currentRow, 'Tellers');

        $this->PaintCell('B'. $this->currentRow, 'firstrow');
        $this->PaintCell('C'. $this->currentRow, 'firstrow');
        $this->PaintCell('A'. $this->currentRow, 'firstrow');
        $this->PaintCell('D'. $this->currentRow, 'firstrow');
        $this->PaintCell('E'. $this->currentRow, 'firstrow');
        $this->PaintCell('F'. $this->currentRow, 'firstrow');
        $this->PaintCell('G'. $this->currentRow, 'firstrow');
        $this->PaintCell('H'. $this->currentRow, 'firstrow');

        $this->Spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $this->Spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(15);
        $this->Spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
        $this->Spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
        $this->Spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
        $this->Spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
        $this->Spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(20);
        $this->Spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(35);

    }

    private function SetCell($cell, $value)
    {
        $this->Spreadsheet->getActiveSheet()->setCellValue($cell, $value);
    }

    private function CreateZaalwachtSchema() {

        $aantalWedstrijden = count($this->WedstrijdenOpDag);
        
        if (is_null($this->ZaalwachtenOpDag)) {
            $bottomRow = $this->currentRow + $aantalWedstrijden + 1;
            $this->tweedeZaalwachtRow = $bottomRow;
            return;
        }

        $this->SetCell('A' . $this->currentRow, "Zaalwachte 1e shift: " . $this->ZaalwachtenOpDag->eersteZaalwacht->naam);
        $this->PaintCell('A' . $this->currentRow, 'zaalwacht');
        $this->PaintCell('B' . $this->currentRow, 'zaalwacht');
        $this->PaintCell('C' . $this->currentRow, 'zaalwacht');
        $this->PaintCell('D' . $this->currentRow, 'zaalwacht');
        $this->PaintCell('E' . $this->currentRow, 'zaalwacht');
        $this->PaintCell('F' . $this->currentRow, 'zaalwacht');
        $this->PaintCell('G' . $this->currentRow, 'zaalwacht');
        $this->PaintCell('H' . $this->currentRow, 'zaalwacht');

        $bottomRow = $this->currentRow + $aantalWedstrijden + 1;

        $this->SetCell('A' . $bottomRow, "Zaalwachte 2e shift: " . $this->ZaalwachtenOpDag->tweedeZaalwacht->naam);
        $this->PaintCell('A' . $bottomRow, 'zaalwacht');
        $this->PaintCell('B' . $bottomRow, 'zaalwacht');
        $this->PaintCell('C' . $bottomRow, 'zaalwacht');
        $this->PaintCell('D' . $bottomRow, 'zaalwacht');
        $this->PaintCell('E' . $bottomRow, 'zaalwacht');
        $this->PaintCell('F' . $bottomRow, 'zaalwacht');
        $this->PaintCell('G' . $bottomRow, 'zaalwacht');
        $this->PaintCell('H' . $bottomRow, 'zaalwacht');

        $this->tweedeZaalwachtRow = $bottomRow;
    }
 
    // Do you think God stays in heaven because he too lives in fear of what he has created? 
    private function SetStandardStyle($cell) {
        $this->Spreadsheet->getActiveSheet()->getRowDimension($cell[1])->setRowHeight(18);
        $cellStyle = $this->Spreadsheet->getActiveSheet()->getStyle($cell);
        $cellStyle->getFont()->setSize(10);
        $cellStyle->getFont()->setName('Arial');
        $cellStyle->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_BLACK));
        $cellStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('36751F');
        $cellStyle->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $cellStyle->getBorders()->getAllBorders()->getColor()->setRGB('000000');

    }

    private function PaintCell($cell, $type)
    {
        // Dit is lelijk maar fuck it
        if ($type == 'firstrow') {
            $this->SetStandardStyle($cell);
            $cellStyle = $this->Spreadsheet->getActiveSheet()->getStyle($cell);
            $this->Spreadsheet->getActiveSheet()->getColumnDimension($cell[0])->setWidth(strlen($this->Spreadsheet->getActiveSheet()->getCell($cell)->getValue()) + 3);
            $cellStyle->getFont()->setBold(true);
            $cellStyle->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));

        }

        elseif ($type == 'zaalwacht') {
            $this->SetStandardStyle($cell);
            $cellStyle = $this->Spreadsheet->getActiveSheet()->getStyle($cell);
            $cellStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('98EF83');
        }

        elseif ($type == 'wedstrijd') {
            $this->SetStandardStyle($cell);
            $this->chooseFieldColour($cell);
        }

        elseif ($type == 'wedstrijd-dag') {
            $this->SetStandardStyle($cell);
            $cellStyle = $this->Spreadsheet->getActiveSheet()->getStyle($cell);
            $cellStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B6D7A8');

        }

    }

    private function chooseFieldColour($cell) {
        $cellStyle = $this->Spreadsheet->getActiveSheet()->getStyle($cell);
        if ($this->kleurNummer % 6 < 3 ) {
            $cellStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('B6D7A8');
        } else {
            $cellStyle->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9EAD3');
        }
    }

    private function CreateBarEnBHVSchema() {

        if (is_null($this->BardienstenEnBHVOpDag)) {
            return;
        }

        //$this->BardienstenEnBHVOpDag->shifts[0/1/2]->isBhv
        //$this->BardienstenEnBHVOpDag->shifts[0/1/2]->naam
        //$this->BardienstenEnBHVOpDag->shifts[0/1/2]->shift

        foreach($this->BardienstenEnBHVOpDag->shifts as $shifts) {
            foreach($shifts->barleden as $barlid) {
                if ($barlid->isBhv) {
                    $this->SetCell('C' . $this->tweedeZaalwachtRow, "BHV: " . $barlid->naam);
                    $this->PaintCell('C' . $this->tweedeZaalwachtRow, 'zaalwacht');
                }
                else {
                    $currentData = substr($this->Spreadsheet->getActiveSheet()->getCell('E' . $this->tweedeZaalwachtRow + $barlid->shift - 1)->getValue(), 11);
                    $currentData = $currentData ? "& " . $currentData : $currentData; // Super hacky maar als er nog geen shift is ingevuld in de cell dan blijft currentData leeg
                    $this->SetCell('E' . $this->tweedeZaalwachtRow + $barlid->shift - 1, "Barshift " . $barlid->shift . ": " . $barlid->naam . $currentData);
                    $this->PaintCell('E' . $this->tweedeZaalwachtRow + $barlid->shift - 1, 'zaalwacht');
                }
            }
        }

        $this->tweedeZaalwachtRow += $barlid->shift - 1;
    }

    private function CheckIfNevoboDataIsComplete($wedstrijd) {
        if (is_null($wedstrijd)) return false;
        if (is_null($wedstrijd->timestamp)) return false;
        if (is_null($wedstrijd->team1)) return false;
        if (is_null($wedstrijd->team1->naam)) return false;
        if (is_null($wedstrijd->team2)) return false;
        if (is_null($wedstrijd->team2->naam)) return false;
        if (is_null($wedstrijd->team1->niveau)) return false;
    }

    private function CreateWedstrijdSchema() {
        $yeet = $this->WedstrijdenOpDag;
        // array of WedstrijdObjecten
        // Wedstrijd = $this->WedstrijdenOpDag[0/1/2/3]->team1->naam
        // Wedstrijd = $this->WedstrijdenOpDag[0/1/2/3]->team2->naam
        // $this->WedstrijdenOpDag[0/1/2/3]->timestamp (Datetime obj)
        // $this->WedstrijdenOpDag[0/1/2/3]->tellers 
        // $this->WedstrijdenOpDag[0/1/2/3]->scheidsrechter
        $this->veldNummer = 1;
        $this->kleurNummer = 0;
        $dayNames = array("Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday");
        $monthNames = array("", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
        


        if (is_null($this->WedstrijdenOpDag)) {
            return;
        }

        $needle = "Aspasia";

        foreach ($this->WedstrijdenOpDag as $wedstrijd) {
            $timestamp = $wedstrijd->timestamp->getTimestamp();
            $formattedTimestamp = $dayNames[date('w', $timestamp)] . " " . date('j', $timestamp) . " " . $monthNames[date('n', $timestamp)];

            if (stripos($wedstrijd->team1->naam, $needle) !== false) {
                print ("hier");
                $yeet = $wedstrijd->team1->naam;
                break;
            }

            $this->SetCell('A' . $this->currentRow, $formattedTimestamp);
            $this->PaintCell('A' . $this->currentRow, 'wedstrijd-dag');

            $this->SetCell('B' . $this->currentRow, $wedstrijd->timestamp->format('H:i'));
            $this->PaintCell('B' . $this->currentRow, 'wedstrijd');

            $this->SetCell('C' . $this->currentRow, $wedstrijd->team1->naam);
            $this->PaintCell('C' . $this->currentRow, 'wedstrijd');

            $this->SetCell('D' . $this->currentRow, $wedstrijd->team2->naam);
            $this->PaintCell('D' . $this->currentRow, 'wedstrijd');

            
            $this->SetCell('E' . $this->currentRow, $this->GetNiveau($wedstrijd->team1->niveau, $wedstrijd->team1->naam));
            $this->PaintCell('E' . $this->currentRow, 'wedstrijd');

            $this->SetCell('F' . $this->currentRow, 'Veld '. $this->veldNummer);
            $this->PaintCell('F' . $this->currentRow, 'wedstrijd');

            $wedstrijd->scheidsrechter->naam = $wedstrijd->scheidsrechter->naam ?? Scheidsrechter;
            if (is_null($wedstrijd->scheidsrechter->naam !== null)) $this->SetCell('G' . $this->currentRow, $wedstrijd->scheidsrechter->naam);
            $this->PaintCell('G' . $this->currentRow, 'wedstrijd');

            $wedstrijd->tellers[0]->naam = $wedstrijd->tellers[0]->naam ?? '';
            $wedstrijd->tellers[1]->naam = $wedstrijd->tellers[1]->naam ?? '';
            if (is_null($wedstrijd->tellers[0]->naam !== null || $wedstrijd->tellers[1]->naam)) $this->SetCell('H' . $this->currentRow, $wedstrijd->tellers[0]->naam . ' & '. $wedstrijd->tellers[1]->naam);
            $this->PaintCell('H' . $this->currentRow, 'wedstrijd');

            $this->veldNummer += 1; // max 3 velden
            $this->veldNummer = $this->veldNummer > 3 ? 1 : $this->veldNummer;
            $this->kleurNummer += 1;
            $this->currentRow += 1;
        }
    }

    private function GetBardienstenEnBHVOpDag() {
        $BarDagenOpDag = $this->BarcieGateway->GetBardag($this->WedstrijdenOpDag[0]->timestamp);
        return $BarDagenOpDag->id != null ? $BarDagenOpDag : null;
    }

    private function GetZaalWachtenOpDag() {
        $ZaalWachtenOpDag = $this->ZaalwachtGateway->GetZaalwacht($this->WedstrijdenOpDag[0]->timestamp);
        return $ZaalWachtenOpDag;
    }
    
    private function GetNiveau($niveau, $naam) {
        if (substr($naam, 4, 2) == "HS") {
            switch ($naam) {
                // Je gaat dit ooit vinden, ik weet niet wat er mis is gegaan maar vanuit de nevobo kregen sommige teams verkeerde
                // niveaus mee dus voor nu gehardcode, bij de dames werkt het wel dusja get rekt.
                case "SKC HS 1":
                    return "3e Divisie";
                case "SKC HS 3":
                case "SKC HS 2":
                    return "1e Klasse";
                case "SKC HS 4":
                    return "2e Klasse";
                case "SKC HS 5":
                case "SKC HS 6":
                    return "3e Klasse";
                case "SKC HS 7":
                case "SKC HS 8":
                case "SKC HS 9":
                    return "4e Klasse";
    
                // mannen for some fucking reason
                // case 0:
                //     return "Eredivisie"; // lol
                // case 1:
                //     return "Superdivisie"; 
                // case 2:
                //     return "Topdivisie"; 
                // case 3:
                //     return "1e Divisie";
                // case 4:
                //     return "2e Divisie";
                // case 5:
                //     return "3e Divisie"; 
                // case 6:
                //     return "Promotie klasse"; 
                // case 7:
                //     return "1e klasse"; 
                // case 8:
                //     return "3e klasse";
                // case 9:
                //     return "4e klasse";
                
                // default:
                //     return "";
                }
            } else {
                switch ($niveau) {
                // vrouwen for some fucking reason
                case 0:
                    return "Eredivisie"; // lol
                case 1:
                    return "Superdivisie"; 
                case 2:
                    return "Topdivisie"; 
                case 3:
                    return "1e Divisie";
                case 4:
                    return "2e Divisie";
                case 5:
                    return "Promotie klasse"; 
                case 6:
                    return "1e klasse"; 
                case 7:
                    return "2e klasse"; 
                case 8:
                    return "3e klasse";
                case 9:
                    return "4e klasse";    
                default:
                    return "";
                }
    
            }
    }

    public function returnExcelExport()
    {
 
    //    $writer = new Xlsx($this->Spreadsheet);
    //    $spreadsheet = new Spreadsheet();
       $writer = IOFactory::createWriter($this->Spreadsheet, 'Xlsx');

       ob_end_clean();
       header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
       header('Content-Disposition: attachment; filename="Teamtakenschema '  . date("d-m-Y") .   '.xlsx"');
       header("Access-Control-Allow-Origin: http://localhost:4200");
       header("Access-Control-Allow-Credentials: true");
       error_log(print_r($this->Spreadsheet, true));
       $writer->save('php://output');
 
    }

 

}