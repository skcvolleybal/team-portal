<?php

namespace TeamPortal\Gateways;

use DateTime;
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
    }

    public function GetStandForPoule(string $poule): array
    {
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
        $url = sprintf($this->pouleprogrammaUrl, $this->regio, $poule, $this->exportType);
        return $this->GetProgramma($url);
    }

    public function GetProgrammaForSporthal(string $sporthal = 'LDNUN'): array
    {
        $url = sprintf($this->sporthalprogrammaUrl, $sporthal, $this->exportType);
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
        $url = sprintf($this->verenigingsprogrammaUrl, $this->verenigingscode, $this->exportType);
        return $this->GetProgramma($url);
    }

    public function GetWedstrijdenForTeam(?Team $team): array
    {
        if (!$team) {
            return [];
        }
        $gender = $this->GetGender($team);
        $sequence = $this->GetSequence($team);
        $url = sprintf($this->teamprogrammaUrl, $this->verenigingscode, $gender, $sequence, $this->exportType);
        return $this->GetProgramma($url);
    }

    public function GetUitslagenForTeam(?Team $team): array
    {
        if (!$team) {
            return [];
        }
        $gender = $this->GetGender($team);
        $sequence = $this->GetSequence($team);
        $url = sprintf($this->teamresultatenUrl, $this->verenigingscode, $gender, $sequence, $this->exportType);
        return $this->GetUitslagen($url);
    }

    public function GetUitslagenForVereniging(): array
    {
        $url = sprintf($this->verenigingsuitslagenUrl, $this->verenigingscode, $this->exportType);
        return $this->GetUitslagen($url);
    }

    public function DoesTeamExist(string $vereniging, string $gender, int $sequence): bool
    {
        $url = sprintf($this->teamprogrammaUrl, $vereniging, $gender, $sequence, $this->exportType);
        $handle = curl_init($url);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);

        /* Get the HTML or whatever is linked in $url. */
        curl_exec($handle);

        /* Check for 404 (file not found). */
        $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
        curl_close($handle);

        return $httpCode == 200;
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

    private function CreateSimplePieFeed(string $url): SimplePie
    {
        $feed = new SimplePie();
        $feed->set_feed_url($url);
        $feed->enable_order_by_date(false);
        $feed->handle_content_type();
        $feed->set_cache_duration($this->cacheDuration);
        if (!file_exists($this->cacheLocation)) {
            mkdir($this->cacheLocation);
        }
        $feed->set_cache_location($this->cacheLocation);
        $feed->init();
        return $feed;
    }

    private function ParseFeed(string $url): array
    {
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
}
