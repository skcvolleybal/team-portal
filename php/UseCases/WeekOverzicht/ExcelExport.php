<?php

namespace TeamPortal\UseCases;

require 'vendor/autoload.php'; // Include Composer's autoloader



use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;



class ExcelExport
{

    function __construct(
        $uscWedstrijden,
        $WedstrijdenOpDag,
        $TelFluitOpDag,
        $BardienstenEnBHVOpDag,
        $ZaalwachtenOpDag
    ) 
    
    {
        $this->uscWedstrijden = $uscWedstrijden;
        $this->WedstrijdenOpDag = $WedstrijdenOpDag;
        $this->TelFluitOpDag = $TelFluitOpDag;
        $this->BardienstenEnBHVOpDag = $BardienstenEnBHVOpDag;
        $this->ZaalwachtenOpDag = $ZaalwachtenOpDag;
    }

    private $tweedeZaalwachtRow; // Wordt gebruikt om bar en bhv dienst mooi neer te zetten, ingevuld in CreateZaalwachtSchema
    private $Spreadsheet;
    private $uscWedstrijden;
    private $WedstrijdenOpDag;
    private $TelFluitOpDag;
    private $BardienstenEnBHVOpDag;
    private $ZaalwachtenOpDag;

    public function GetExcelExport() {
        // Init the spreadsheet
        $currentRow = 1;
        $this->Spreadsheet = new Spreadsheet();
        $this->Spreadsheet->setActiveSheetIndex(0);
        $this->CreateFirstRow();
        $currentRow += 1;

        $this->CreateZaalwachtSchema($currentRow);
        $currentRow += 1;

        $this->CreateWedstrijdSchema($currentRow);

        $this->CreateBarEnBHVSchema($currentRow);

    }

    // Creates the first row that is always the same: has Wedstrijd, Tijd, Team A, Team B, Niveau, Veldnummer, Scheidrechter en Tellers als text
    private function CreateFirstRow() {
        $this->SetCell('A1', 'Wedstrijd');
        $this->SetCell('B1', 'Tijd');
        $this->SetCell('C1', 'Team A');
        $this->SetCell('D1', 'Team B');
        $this->SetCell('E1', 'Niveau');
        $this->SetCell('F1', 'Veldnummer');
        $this->SetCell('G1', 'Scheidsrechter');
        $this->SetCell('H1', 'Tellers');

        $this->PaintCell('B1', 'firstrow');
        $this->PaintCell('C1', 'firstrow');
        $this->PaintCell('A1', 'firstrow');
        $this->PaintCell('D1', 'firstrow');
        $this->PaintCell('E1', 'firstrow');
        $this->PaintCell('F1', 'firstrow');
        $this->PaintCell('G1', 'firstrow');
        $this->PaintCell('H1', 'firstrow');

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

    private function CreateZaalwachtSchema($currentRow) {
        $aantalWedstrijden = count($this->WedstrijdenOpDag);

        $this->SetCell('A' . $currentRow, "Zaalwachte 1e shift: " . $this->ZaalwachtenOpDag->eersteZaalwacht->naam);
        $this->PaintCell('A' . $currentRow, 'zaalwacht');

        $bottomRow = $currentRow + $aantalWedstrijden + 1;

        $this->SetCell('A' . $bottomRow, "Zaalwachte 2e shift: " . $this->ZaalwachtenOpDag->tweedeZaalwacht->naam);
        $this->PaintCell('A' . $bottomRow, 'zaalwacht');

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
        }

        elseif ($type == 'wedstrijd') {
            $this->SetStandardStyle($cell);
        }
    }

    private function CreateBarEnBHVSchema($currentRow) {
        $yeet = $this->BardienstenEnBHVOpDag;
        //$this->BardienstenEnBHVOpDag->shifts[0/1/2]->isBhv
        //$this->BardienstenEnBHVOpDag->shifts[0/1/2]->naam
        //$this->BardienstenEnBHVOpDag->shifts[0/1/2]->shift
        $hbuyifhun = $this->BardienstenEnBHVOpDag->shifts->barleden;

        foreach($this->BardienstenEnBHVOpDag->shifts as $shifts) {
            foreach($shifts->barleden as $barlid) {
                if ($barlid->isBhv) {
                    $this->SetCell('C' . $this->tweedeZaalwachtRow, "BHV: " . $barlid->naam);
                    $this->PaintCell('C' . $this->tweedeZaalwachtRow, 'wedstrijd');
                }
                else {
                    $currentData = substr($this->Spreadsheet->getActiveSheet()->getCell('E' . $this->tweedeZaalwachtRow + $barlid->shift - 1)->getValue(), 11);
                    $currentData = $currentData ? "& " . $currentData : $currentData; // Super hacky maar als er nog geen shift is ingevuld in de cell dan blijft currentData leeg
                    $this->SetCell('E' . $this->tweedeZaalwachtRow + $barlid->shift - 1, "Barshift " . $barlid->shift . ": " . $barlid->naam . $currentData);
                    $this->PaintCell('E' . $this->tweedeZaalwachtRow + $barlid->shift - 1, 'wedstrijd');
                }
            }
        }

        $this->tweedeZaalwachtRow += $barlid->shift - 1;
    }

    private function CreateWedstrijdSchema($currentRow) {
        // array of WedstrijdObjecten
        // Wedstrijd = $this->WedstrijdenOpDag[0/1/2/3]->team1->naam
        // Wedstrijd = $this->WedstrijdenOpDag[0/1/2/3]->team2->naam
        // $this->WedstrijdenOpDag[0/1/2/3]->timestamp (Datetime obj)
        // $this->WedstrijdenOpDag[0/1/2/3]->tellers 
        // $this->WedstrijdenOpDag[0/1/2/3]->scheidsrechter
        $veldnummer = 1;
        foreach ($this->WedstrijdenOpDag as $wedstrijd) {
            $this->SetCell('A' . $currentRow, $wedstrijd->timestamp->format('Y-m-d'));
            $this->PaintCell('A' . $currentRow, 'wedstrijd');

            $this->SetCell('B' . $currentRow, $wedstrijd->timestamp->format('H:i:s'));
            $this->PaintCell('B' . $currentRow, 'wedstrijd');

            $this->SetCell('C' . $currentRow, $wedstrijd->team1->naam);
            $this->PaintCell('C' . $currentRow, 'wedstrijd');

            $this->SetCell('D' . $currentRow, $wedstrijd->team2->naam);
            $this->PaintCell('D' . $currentRow, 'wedstrijd');

            $this->SetCell('E' . $currentRow, $this->GetNiveau($wedstrijd->team1->naam));
            $this->PaintCell('E' . $currentRow, 'wedstrijd');

            $this->SetCell('F' . $currentRow, 'Veld '. $veldnummer);
            $this->PaintCell('F' . $currentRow, 'wedstrijd');

            $this->SetCell('G' . $currentRow, $wedstrijd->scheidsrechter->naam);
            $this->PaintCell('G' . $currentRow, 'wedstrijd');

            $this->SetCell('H' . $currentRow, $wedstrijd->tellers[0]->naam . ' & '. $wedstrijd->tellers[1]->naam);
            $this->PaintCell('H' . $currentRow, 'wedstrijd');

            $veldnummer += 1;
            $currentRow += 1;
        }




    }

    private function GetNiveau($teamNaam) {
        switch ($teamNaam) {
            // HEREN
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

            // DAMES
            case "SKC DS 1":
                return "Promotieklasse";
            case "SKC DS 2":
            case "SKC DS 3":
            case "SKC DS 4":
                return "1e Klasse";
            case "SKC DS 5":
            case "SKC DS 6":
            case "SKC DS 7":
                return "2e Klasse";
            
            case "SKC DS 8":
            case "SKC DS 9":
            case "SKC DS 10":
            case "SKC DS 11":
            case "SKC DS 12":
                return "3e Klasse";
            case "SKC DS 13":
            case "SKC DS 14":
            case "SKC DS 15":
                return "4e Klasse";
            }
    }

    public function returnExcelExport()
    {
 
    //    $writer = new Xlsx($this->Spreadsheet);
    //    $spreadsheet = new Spreadsheet();
       $writer = IOFactory::createWriter($this->Spreadsheet, 'Xlsx');

       ob_end_clean();
       header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
       header('Content-Disposition: attachment; filename="TC-indeling '  . date("d-m-Y") .   '.xlsx"');
       header("Access-Control-Allow-Origin: http://localhost:4200");
       header("Access-Control-Allow-Credentials: true");
       error_log(print_r($this->Spreadsheet, true));
       $writer->save('php://output');
 
    }

 

}