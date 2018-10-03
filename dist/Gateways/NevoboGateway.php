<?php
require_once 'simplepie-1.5/autoloader.php';

class NevoboGateway
{
    public $cacheDuration = 3600 * 24; // 24 uur
    public $cacheLocation = './cache';

    private $pouleprogrammaUrl = 'https://api.nevobo.nl/export/poule/%s/%s/programma.%s';
    private $pouleresultatenUrl = 'https://api.nevobo.nl/export/poule/%s/%s/resultaten.%s';
    private $poulestandUrl = 'https://api.nevobo.nl/export/poule/%s/%s/stand.%s';
    private $verenigingsprogrammaUrl = 'https://api.nevobo.nl/export/vereniging/%s/programma.%s';
    private $verenigingsresultatenUrl = 'https://api.nevobo.nl/export/vereniging/%s/resultaten.%s';
    private $teamprogrammaUrl = 'https://api.nevobo.nl/export/team/%s/%s/%s/programma.%s';
    private $teamresultatenUrl = 'https://api.nevobo.nl/export/team/%s/%s/%s/resultaten.%s';
    private $sporthalprogrammaUrl = 'https://api.nevobo.nl/export/sporthal/%s/programma.%s';
    private $sporthalResultatenUrl = 'https://api.nevobo.nl/export/sporthal/%s/resultaten.%s';
    private $xmlns = 'https://www.nevobo.nl/competitie/';

    private $verenigingscode;
    private $regio;

    private $exportType = 'rss';
    private $monthTranslations = [
        'januari' => 'January', 'februari' => 'February', 'maart' => 'March', 'april' => 'April',
        'mei' => 'May', 'juni' => 'June', 'juli' => 'July', 'augustus' => 'August',
        'september' => 'September', 'oktober' => 'October', 'november' => 'November', 'december' => 'December',
    ];

    public function __construct($verenigingscode = 'CKL9R53', $regio = 'regio-west')
    {
        $this->verenigingscode = $verenigingscode;
        $this->regio = $regio;
    }

    public function GetStandForTeam($poule)
    {
        $url = sprintf($this->poulestandUrl, $this->regio, $poule, $this->exportType);

        $feed = $this->CreateSimplePieFeed($url);
        $rankings = $feed->get_channel_tags($this->xmlns, 'ranking');

        $results = [];
        foreach ($rankings as $ranking) {
            $nummer = $ranking['child'][$this->xmlns]['nummer'][0]['data'];
            $team = $ranking['child'][$this->xmlns]['team'][0]['data'];
            $wedstrijden = $ranking['child'][$this->xmlns]['wedstrijden'][0]['data'];
            $punten = $ranking['child'][$this->xmlns]['punten'][0]['data'];
            $setsVoor = $ranking['child'][$this->xmlns]['setsvoor'][0]['data'];
            $setsTegen = $ranking['child'][$this->xmlns]['setstegen'][0]['data'];
            $puntenVoor = $ranking['child'][$this->xmlns]['puntenvoor'][0]['data'];
            $puntenTegen = $ranking['child'][$this->xmlns]['puntentegen'][0]['data'];

            $results[] = [
                'nummer' => $nummer,
                'team' => $team,
                'wedstrijden' => $wedstrijden,
                'punten' => $punten,
                'setsVoor' => $setsVoor,
                'setsTegen' => $setsTegen,
                'puntenVoor' => $puntenVoor,
                'puntenTegen' => $puntenTegen,
            ];
        }

        return $results;
    }

    public function GetProgrammaForPoule($poule)
    {
        $url = sprintf($this->pouleprogrammaUrl, $this->regio, $poule, $this->exportType);
        return $this->GetProgramma($url);
    }

    public function GetProgrammaForSporthal($sporthal)
    {
        $url = sprintf($this->sporthalprogrammaUrl, $sporthal, $this->exportType);
        return $this->GetProgramma($url);
    }

    public function GetProgrammaForVereniging($vereniging)
    {
        $url = sprintf($this->verenigingsprogrammaUrl, $vereniging, $this->exportType);
        return $this->GetProgramma($url);
    }

    public function GetProgrammaForTeam($team)
    {
        $gender = $this->GetGender($team);
        $sequence = $this->GetSequence($team);
        $url = sprintf($this->teamprogrammaUrl, $this->verenigingscode, $gender, $sequence, $this->exportType);
        return $this->GetProgramma($url);
    }

    public function DoesTeamExist($vereniging, $gender, $sequence)
    {
        $url = sprintf($this->teamprogrammaUrl, $vereniging, $gender, $sequence, $this->exportType);
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        /* Get the HTML or whatever is linked in $url. */
        $response = curl_exec($handle);

        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        return $httpCode == 200;
    }

    public function GetLowestTeamOf($gender)
    {
        if ($gender == 'heren' || $gender == 'dames') {
            $currentTeamExists = $this->DoesTeamExist($this->verenigingscode, $gender, 10);
            if ($currentTeamExists) {
                for ($i = 11; $i < 50; $i++) {
                    $currentTeamExists = $this->DoesTeamExist($this->verenigingscode, $gender, $i);
                    if (!$currentTeamExists) {
                        return $i - 1;
                    }
                }
            } else {
                for ($i = 9; $i >= 1; $i--) {
                    $currentTeamExists = $this->DoesTeamExist($this->verenigingscode, $gender, $i);
                    if ($currentTeamExists) {
                        return $i;
                    }
                }
            }
        }

        return -1;
    }

    public function Test()
    {
        echo "Programma voor poule: ";
        $count = count($this->GetProgrammaForPoule('H1E'));
        if ($count > 0) {
            echo "Gelukt ($count)<br \>";
        } else {
            echo "Gefaald<br \>";
        }

        echo "Programma voor sporthal: ";
        $count = count($this->GetProgrammaForSporthal('LDNUN'));
        if ($count > 0) {
            echo "Gelukt ($count)<br \>";
        } else {
            echo "Gefaald<br \>";
        }

        echo "Programma voor vereniging: ";
        $count = count($this->GetProgrammaForVereniging('CKL9R53'));
        if ($count > 0) {
            echo "Gelukt ($count)<br \>";
        } else {
            echo "Gefaald<br \>";
        }

        echo "Programma voor team: ";
        $count = count($this->GetProgrammaForTeam('CKL9R53', 'heren', 1));
        if ($count > 0) {
            echo "Gelukt ($count)<br \>";
        } else {
            echo "Gefaald<br \>";
        }

        echo "Stand voor team: ";
        $count = count($this->GetStandForTeam('H1E'));
        if ($count > 0) {
            echo "Gelukt ($count)<br \>";
        } else {
            echo "Gefaald<br \>";
        }

        echo "Dames 44 bestaat " . ($this->DoesTeamExist('CKL9R53', 'dames', 44) ? "wel" : "niet") . "<br />";
        echo "Heren 2 bestaat " . ($this->DoesTeamExist('CKL9R53', 'heren', 2) ? "wel" : "niet") . "<br />";

        echo "Het laagste dames team is: Dames " . $this->GetLowestTeamOf('dames') . "<br \>";
        echo "Het laagste heren team is: Heren " . $this->GetLowestTeamOf('heren') . "<br \>";
    }

    private function GetGender($team)
    {
        if (substr($team, 4, 2) == "HS") {
            return 'heren';
        }

        if (substr($team, 4, 2) == "DS") {
            return 'dames';
        }

        throw new Exception("Unknown gender for team '$team'");
    }

    private function GetSequence($team)
    {
        $sequence = substr($team, 7);
        if (empty($sequence)) {
            throw new Exception("Unknown sequence for team '$team'");
        }

        return $sequence;
    }

    private function GetProgramma($url)
    {
        /*
        Voorbeeld:

        [title] =>
        20 sep. 21:00: VCS HS 5 - SKC HS 7
        [description] =>
        Wedstrijd: 3000H4G BK, Datum: donderdag 20 september, 21:00, Speellocatie: Wasbeek, Van Alkemadelaan 12, 2171DH SASSENHEIM
         */

        $matches = $this->ParseFeed($url);
        $programma = [];
        foreach ($matches as $match) {
            $title = addslashes($match['title']);
            $description = addslashes($match['description']);

            if (strpos($title, 'Uitslag: ') !== false) {
                continue;
            }

            preg_match("/(.*): (.*) - (.*)/", $title, $titleMatches);
            $team1 = stripslashes($titleMatches[2]);
            $team2 = stripslashes($titleMatches[3]);

            preg_match("/Wedstrijd: (.*), Datum: (.*), Speellocatie: (.*)/", $description, $descriptionMatches);
            $matchId = preg_replace('/\s+/', ' ', $descriptionMatches[1]);
            $date = $descriptionMatches[2];
            $locatie = preg_replace('/\s+/', ' ', stripslashes($descriptionMatches[3]));

            $programma[] = [
                'team1' => $team1,
                'team2' => $team2,
                'id' => $matchId,
                'poule' => substr($matchId, 4, 3),
                'timestamp' => $this->ConvertNevoboDate($date),
                'locatie' => $locatie,
            ];
        }

        return $programma;
    }

    private function ConvertNevoboDate($date)
    {
        /* Voorbeeld: donderdag 20 september, 21:00 */

        if (empty($date)) {
            return null;
        }

        if (!preg_match("/(.*) (.*) (.*), (.*):(.*)/", $date, $dateMatches)) {
            return "Unparseble date: $date";
        }
        $day = $dateMatches[2];
        $month = $dateMatches[3];
        $hours = $dateMatches[4];
        $minutes = $dateMatches[5];

        if (!array_key_exists(strtolower($month), $this->monthTranslations)) {
            return "Unknown month: $month";
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

    private function CreateSimplePieFeed($url)
    {
        $feed = new SimplePie();
        $feed->set_feed_url($url);
        $feed->enable_order_by_date(false);
        $feed->handle_content_type();
        $feed->set_cache_duration($this->cacheDuration);
        $feed->set_cache_location($this->cacheLocation);
        $feed->init();
        return $feed;
    }

    private function ParseFeed($url)
    {
        $feed = $this->CreateSimplePieFeed($url);

        $result = [];
        for ($i = 0; $i < $feed->get_item_quantity(); $i++) {
            $result[] = [
                'title' => $feed->get_item($i)->get_title(),
                'description' => $feed->get_item($i)->get_description(),
            ];
        }

        return $result;
    }
}
