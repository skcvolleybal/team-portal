<?php

namespace TeamPortal\Gateways;

use DateTime;
use InvalidArgumentException;
use SimplePie;
use TeamPortal\Common\DateFunctions;
use TeamPortal\Common\Utilities;
use TeamPortal\Entities\Speeltijd;
use TeamPortal\Entities\Stand;
use TeamPortal\Entities\Team;
use TeamPortal\Entities\Wedstrijd;
use TeamPortal\Entities\Wedstrijddag;
use TeamPortal\UseCases\INevoboGateway;
use UnexpectedValueException;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;


// error_reporting(E_ALL ^ E_DEPRECATED); // Suppress warnings on PHP 8.0. Make sure to fix the usort() functions in this file for PHP 8.1. 


class NevoboGateway implements INevoboGateway
{
    public $cacheDuration = 3600 * 24; // 24 uur
    public $cacheLocation = './cache';

    private $pouleprogrammaUrl = 'https://api.nevobo.nl/export/poule/%s/%s/programma.%s';
    private $poulestandUrl = 'https://api.nevobo.nl/export/poule/%s/%s/stand.%s';
    private $verenigingsprogrammaUrl = 'https://api.nevobo.nl/export/vereniging/%s/programma.%s';
    private $verenigingsuitslagenUrl = 'https://api.nevobo.nl/export/vereniging/%s/resultaten.rss';
    private $teamprogrammaUrl = 'https://api.nevobo.nl/export/team/%s/%s/%s/programma.%s';
    private $teamresultatenUrl = 'https://api.nevobo.nl/export/team/%s/%s/%s/resultaten.%s';
    private $sporthalprogrammaUrl = 'https://api.nevobo.nl/export/sporthal/%s/programma.%s';
    private $xmlns = 'https://www.nevobo.nl/competitie/';

    private $verenigingscode;
    private $regio;

    private $useMockData;

    private $exportType = 'rss';
    private $monthTranslations = [
        'januari' => 'January',
        'februari' => 'February',
        'maart' => 'March',
        'april' => 'April',
        'mei' => 'May',
        'juni' => 'June',
        'juli' => 'July',
        'augustus' => 'August',
        'september' => 'September',
        'oktober' => 'October',
        'november' => 'November',
        'december' => 'December',
    ];

    public function __construct(string $verenigingscode = 'CKL9R53', string $regio = 'regio-west')
    {
        $this->verenigingscode = $verenigingscode;
        $this->regio = $regio;
        $this->useMockData = ($_ENV['USEMOCKDATA'] == 'true') ? true : false;
    }

    public function GetStandForPoule(string $poule): array
    {
        if ($this->useMockData) {
            return $this->loadMockData('standForPoule.rss');
        }

        $url = sprintf($this->poulestandUrl, $this->regio, $poule, $this->exportType);

        $feed = $this->CreateSimplePieFeed($url);
        $rankings = $feed->get_channel_tags($this->xmlns, 'ranking');

        $results = [];
        foreach ($rankings as $ranking) {
            $team = $ranking['child'][$this->xmlns];

            $nummer = Utilities::StringToInt($team['nummer'][0]['data']);
            $teamnaam = $team['team'][0]['data'];
            $wedstrijden = $team['wedstrijden'][0]['data'];
            $punten = $team['punten'][0]['data'];
            $setsVoor = $team['setsvoor'][0]['data'];
            $setsTegen = $team['setstegen'][0]['data'];
            $puntenVoor = $team['puntenvoor'][0]['data'];
            $puntenTegen = $team['puntentegen'][0]['data'];

            $results[] = new Stand(
                $nummer,
                new Team($teamnaam),
                $wedstrijden,
                $punten,
                $setsVoor,
                $setsTegen,
                $puntenVoor,
                $puntenTegen
            );
        }

        return $results;
    }

    public function GetProgrammaForPoule(string $poule): array
    {
        if ($this->useMockData) {
            return $this->loadMockData('programmaForPoule.rss');
        }

        $url = sprintf($this->pouleprogrammaUrl, $this->regio, $poule, $this->exportType);
        return $this->GetProgramma($url);
    }

    public function GetProgrammaForSporthal(string $sporthal = 'LDNUN'): array
    {
        $url = sprintf($this->sporthalprogrammaUrl, $sporthal, $this->exportType);
        
        if ($this->useMockData) {
            $url = $this->getMockDataPath('programmaForSporthal.rss');
        
        }   
        return $this->GetProgramma($url);
    }

    public function GetWedstrijddagenForSporthal(string $sporthal = 'LDNUN', int $dagen = 7): array
    {

        $endDate = new DateTime("+$dagen days");
        $wedstrijden = $this->GetProgrammaForSporthal($sporthal);
        usort($wedstrijden, [Wedstrijd::class, "Compare"]);
        $wedstrijddagen = [];
        $currentDag = null;
        $currentSpeeltijd = null;
        foreach ($wedstrijden as $wedstrijd) {
            if ($wedstrijd->timestamp === null || $wedstrijd->timestamp > $endDate) {
                continue;
            }
            if ($currentDag !== DateFunctions::GetYmdNotation($wedstrijd->timestamp)) {
                $currentDag = DateFunctions::GetYmdNotation($wedstrijd->timestamp);
                $currentSpeeltijd = null;
                $wedstrijddagen[] = new Wedstrijddag(DateFunctions::CreateDateTime($currentDag));
            }
            $i = count($wedstrijddagen) - 1;

            if ($currentSpeeltijd !== DateFunctions::GetTime($wedstrijd->timestamp)) {
                $currentSpeeltijd = DateFunctions::GetTime($wedstrijd->timestamp);
                $wedstrijddagen[$i]->speeltijden[] = new Speeltijd(DateFunctions::CreateDateTime($currentDag, $currentSpeeltijd));
            }

            $wedstrijddagen[$i]->AddWedstrijd($wedstrijd);
        }
        return $wedstrijddagen;
    }

    public function GetProgrammaForVereniging(): array
    {
        if ($this->useMockData) {
            return $this->loadMockData('programmaForVereniging.rss');
        }

        $url = sprintf($this->verenigingsprogrammaUrl, $this->verenigingscode, $this->exportType);
        return $this->GetProgramma($url);
    }

    public function GetWedstrijdenForTeam(?Team $team): array
    {
        $gender = $this->GetGender($team);
        $sequence = $this->GetSequence($team);
        $url = sprintf($this->teamprogrammaUrl, $this->verenigingscode, $gender, $sequence, $this->exportType);

        if ($this->useMockData) {
            $url = $this->getMockDataPath('wedstrijdenForTeam.rss');

        }
        return $this->GetProgramma($url);
    }


    public function GetVerenigingsStanden () {

        $url = 'https://api.nevobo.nl/export/vereniging/' . $this->verenigingscode . '/stand.xlsx';
        
        // Use file_get_contents to download the file
        $content = file_get_contents($url);

        if ($content === false) {
            // Handle error, file could not be downloaded
            die("Error: Unable to download the Excel file.");
        }

        // Save the content to a temporary file
        $tmpfname = tempnam(sys_get_temp_dir(), 'excel');
        
        file_put_contents($tmpfname, $content);

        // Load the Excel file
        $spreadsheet = IOFactory::load($tmpfname);

        // Now you can work with the spreadsheet, for example, read data
        $sheet = $spreadsheet->getActiveSheet();
 
        $teams = [];



        // Get the teams and poule names
        foreach ($sheet->getRowIterator() as $row) {
            $cellIterator = $row->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true); // Loop only existing cells

            // Get the value of the first cell
            $firstCellValue = $cellIterator->current()->getValue();

            // Check if the first cell contains 'SKC'
            if (strpos($firstCellValue, 'SKC') !== false) {
                // Extract the values of the entire row
                $rowData = [];
                foreach ($cellIterator as $cell) {
                    $rowData[] = $cell->getValue();
                }
                // Process the extracted row data (e.g., print, store in an array, etc.)
                $teams[] = $rowData;
            }

            foreach ($teams as $key => $team) {
                $teams[$key][1] = str_replace("Seniorencompetitie, ", "", $team[1]);
            }   
        }

         
        // Get the teams scores
        $teamScores = [];
        try {
            $sheet = $spreadsheet->getActiveSheet();
        
            // Loop through each team
            foreach ($teams as $team) {
                $teamName = $team[0]; // Get the team name
        
                // Loop through each row of the sheet
                foreach ($sheet->getRowIterator() as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false); // This loops through all cells
        
                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $rowData[] = $cell->getValue();
                    }
        
                    // Check if the team name is in the second column
                    if (isset($rowData[1]) && $rowData[1] == $teamName) {
                        // Extract and process the row data as needed
                        // print_r($rowData); // For demonstration purposes
                        $teamScores[] = $rowData;
                    }
                }
            }
        } catch (Exception $e) {
            echo 'Error loading spreadsheet file: ' . $e->getMessage();
        }

        // Define the new keys
        $newKeys = [
            'Ranking', 'Teamnaam', 'Wedstrijden', 'Punten', 
            'Sets_voor', 'Sets_tegen', 'Punten_voor', 'Punten_tegen', 'Opmerkingen'
        ];

        // Iterate over each sub-array and assign new keys
        foreach ($teamScores as &$subArray) {
            $subArray = array_combine($newKeys, $subArray);
        }

        
        // Loop through each team in $teamScores
        foreach ($teamScores as $key => $teamScore) {
            // Extract the teamname from the current team in $teamScores
            $teamName = $teamScore['Teamnaam'];

            // Search for the team in the $teams array
            foreach ($teams as $team) {
                if ($team[0] == $teamName) {
                    // Extract the Niveau and perform the required string manipulations
                    $niveau = $team[1];
                    $niveau = str_replace(' klasse', ' Klasse', $niveau); // Replace "klasse" with "Klasse"
                    $niveau = preg_replace('/\b(Heren|Dames)\b/', '', $niveau); // Remove "Heren" or "Dames"
                    $niveau = trim($niveau); // Remove leading and trailing spaces
                    $niveau = preg_replace('/\s+[A-Za-z]$/','', $niveau); // Remove loose characters at the end

                    // Add the modified "Niveau" key to the $teamScores array
                    $teamScores[$key]['Niveau'] = $niveau;
                    break;
                }
            }
        }
        // Array to keep track of unique team names
        $uniqueTeamNames = [];

        // Array to hold the final result
        $filteredArray = [];

        foreach ($teamScores as $teamScore) {
            // Check if the Teamnaam value is already in the uniqueTeamNames array
            if (!in_array($teamScore['Teamnaam'], $uniqueTeamNames)) {
                // If not, add it to uniqueTeamNames and filteredArray
                $uniqueTeamNames[] = $teamScore['Teamnaam'];
                $filteredArray[] = $teamScore;
            }
        }

        
        usort($filteredArray, function ($a, $b) {
            return $a['Ranking'] <=> $b['Ranking'];
        });

        // Then sort by Punten within each Ranking
        usort($filteredArray, function($a, $b) {
            if ($a['Ranking'] == $b['Ranking']) {
                return $b['Punten'] <=> $a['Punten']; // Note: this sorts in descending order of Punten
            }
            return $a['Ranking'] <=> $b['Ranking'];
        });

        

        
        // Remove the temporary file
        unlink($tmpfname);

        // $sortedData now contains associative arrays with unique Teamnaam values
        return $filteredArray;

    }


    private function GetGender(Team $team): string
    {
        if (substr($team->naam, 4, 2) == 'HS') {
            return 'heren';
        }

        if (substr($team->naam, 4, 2) == 'DS') {
            return 'dames';
        }

        throw new InvalidArgumentException("Onbekend geslacht in team '$team'");
    }

    private function GetSequence(Team $team): int
    {
        $sequence = substr($team->naam, 7);
        if (empty($sequence)) {
            throw new InvalidArgumentException("Unknown sequence for team '$team->naam'");
        }

        return $sequence;
    }

    private function GetProgramma(string $url): array
    {
        /*
        Voorbeeld:

        [title] =>
        20 sep. 21:00: VCS HS 5 - SKC HS 7
        [description] =>
        Wedstrijd: 3000H4G BK, Datum: donderdag 20 september, 21:00, Speellocatie: Wasbeek, Van Alkemadelaan 12, 2171DH SASSENHEIM


        OF:
        Vervallen wedstrijd: 3000D4G   KJ, Datum: vrijdag 24 januari, 21:30, Speellocatie: Universitair SC, Einsteinweg 6, 2333CC  LEIDEN
         */

        $matches = $this->ParseFeed($url);
        $programma = [];
        foreach ($matches as $match) {
            $title = addslashes($match->title);
            $description = addslashes($match->description);

            if (strpos($title, 'Uitslag: ') !== false) {
                continue;
            }

            preg_match('/(.*): (.*) - (.*)/', $title, $titleMatches);
            $team1 = stripslashes($titleMatches[2]);
            $team2 = stripslashes($titleMatches[3]);

            if (preg_match('/Wedstrijd: (.*), Datum: (.*), Speellocatie: (.*)/', $description, $descriptionMatches)) {
                $date = $this->ConvertNevoboDate($descriptionMatches[2]);
                $matchId = preg_replace('/\s+/', ' ', $descriptionMatches[1]);
                $locatie = preg_replace('/\s+/', ' ', stripslashes($descriptionMatches[3]));

                $programma[] = Wedstrijd::CreateFromNevoboWedstrijd(
                    $matchId,
                    new Team($team1),
                    new Team($team2),
                    substr($matchId, 4, 3),
                    $date,
                    $locatie
                );
            } else if (preg_match('/Vervallen wedstrijd: (.*), Datum: (.*), (.*), Speellocatie: (.*), (.*)/', $description, $descriptionMatches)) {
                // nothing
            }
        }

        return $programma;
    }

    private function GetUitslagen(string $url): array
    {
        /*
        Voorbeeld:

        [title] =>
        SKC HS 2 - Aspasia HS 2, Uitslag: 3-2
        [description] =>
        Wedstrijd: SKC HS 2 - Aspasia HS 2, Uitslag: 3-2, Setstanden: 27-25, 17-25, 19-25, 25-16, 15-10
         */

        $matches = $this->ParseFeed($url);
        $uitslagen = [];
        foreach ($matches as $match) {
            $title = addslashes($match->title);
            $description = addslashes($match->description);

            if (preg_match('/(.*) - (.*), Uitslag: (.*)/', $title, $titleMatches)) {
                $team1 = stripslashes($titleMatches[1]);
                $team2 = stripslashes($titleMatches[2]);
                $uitslag = $titleMatches[3];

                preg_match('/Wedstrijd: (.*), Uitslag: (.*), Setstanden: (.*)/', $description, $descriptionMatches);
                $setstanden = $descriptionMatches[3];

                $wedstrijd = new Wedstrijd("geen");
                $wedstrijd->timestamp = DateFunctions::CreateDateTime(substr($match->date, 0, 10), substr($match->date, 11, 8));
                $wedstrijd->team1 = new Team($team1);
                $wedstrijd->team2 = new Team($team2);
                $wedstrijd->uitslag = $uitslag;
                $wedstrijd->setstanden = $setstanden;
                $uitslagen[] = $wedstrijd;
            } else if (preg_match('/Vervallen wedstrijd: (.*), Datum: (.*), (.*), Speellocatie: (.*), (.*)/', $description, $descriptionMatches)) {
                // Nothing
            }
        }

        return $uitslagen;
    }

    private function ConvertNevoboDate(string $date): ?DateTime
    {
        /* Voorbeeld: donderdag 20 september, 21:00 */
        if (empty($date)) {
            return null;
        }

        if (!preg_match('/(.*) (.*) (.*), (.*):(.*)/', $date, $dateMatches)) {
            throw new UnexpectedValueException('Unparseble date: $date');
        }
        $day = $dateMatches[2];
        $month = $dateMatches[3];
        $hours = $dateMatches[4];
        $minutes = $dateMatches[5];

        if (!array_key_exists(strtolower($month), $this->monthTranslations)) {
            throw new UnexpectedValueException('Unknown month: $month');
        }

        $month = $this->monthTranslations[$month];

        $currentYear = date('Y');

        $isMatchInfirstSixMonths = in_array($month, ['January', 'February', 'March', 'April', 'May', 'June']);

        // Is today in first six months?
        if (date('n') < 7) {
            if ($isMatchInfirstSixMonths) {
                $year = $currentYear;
            } else {
                $year = $currentYear - 1;
            }
        } else {
            if ($isMatchInfirstSixMonths) {
                $year = $currentYear + 1;
            } else {
                $year = $currentYear;
            }
        }

        return DateTime::createFromFormat('d F Y H:i', "$day $month $year $hours:$minutes");
    }

    private function isLocalFile($path): bool {
        // Check if the path is a local file path by looking for the presence of 'http'
        return strpos($path, 'http') === false;
    }
    

    private function CreateSimplePieFeed(string $url): SimplePie {
        $feed = new SimplePie();
        
        if ($this->isLocalFile($url)) {
            // If it's a local file, read its contents and use set_raw_data
            if (file_exists($url)) {
                $fileContents = file_get_contents($url);
                $feed->set_raw_data($fileContents);
            } else {
                // Handle error: file not found
                throw new Exception("File not found: $url");
            }
        } else {
            // If it's a URL, set it directly
            $feed->set_feed_url($url);
        }
    
        $feed->init();
        $feed->handle_content_type();
        
        return $feed;
    }
    

    private function ParseFeed(string $url): array {
        $feed = $this->CreateSimplePieFeed($url);
    
        $result = [];
        for ($i = 0; $i < $feed->get_item_quantity(); $i++) {
            $rssFeedItem = new RssFeedItem;
            $rssFeedItem->title = $feed->get_item($i)->get_title();
            $rssFeedItem->date = $feed->get_item($i)->get_date("Y-m-d G:i:s");
            $rssFeedItem->description = $feed->get_item($i)->get_description();            
            $result[] = $rssFeedItem;
        }
        
        return $result;
    }
    
    private function getMockDataPath($filename) : string {
        $path = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'mocks' . DIRECTORY_SEPARATOR . $filename;
        // Normalize the file path for URL
        $normalizedFilePath = str_replace('\\', '/', $path); // Convert backslashes to forward slashes
    
        // Detect if running on Windows and adjust the file URL protocol accordingly
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows paths need an extra '/' if they start with a drive letter, and we don't trim the initial slash
            $feedUrl = 'file:///' . $normalizedFilePath;
        } else {
            // For Unix/Linux, use two slashes
            $feedUrl = 'file://' . $normalizedFilePath;
        }
    
        return $feedUrl;
    }
    
}
