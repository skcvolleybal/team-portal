<?php

namespace TeamPortal\Gateways;

use DOMDocument;
use DOMElement;
use DOMNodeList;
use DOMXPath;
use TeamPortal\Common\Curl;
use TeamPortal\Common\Request;
use TeamPortal\Configuration;
use TeamPortal\Entities\Credentials;
use TeamPortal\Entities\DwfKaart;
use TeamPortal\Entities\DwfOpstelling;
use TeamPortal\Entities\DwfPunt;
use TeamPortal\Entities\DwfSet;
use TeamPortal\Entities\DwfSpeler;
use TeamPortal\Entities\DwfSpelophoud;
use TeamPortal\Entities\DwfTimeout;
use TeamPortal\Entities\DwfWedstrijd;
use TeamPortal\Entities\DwfWissel;
use TeamPortal\Entities\Punttype;
use TeamPortal\Entities\Team;
use TeamPortal\Entities\ThuisUit;
use UnexpectedValueException;

class DwfGateway
{
    private $dwfUrl = 'https://dwf.volleybal.nl/application/handlers/dwf/pull/';
    private $dwfOAuthUrl = 'https://dwf.volleybal.nl/application/handlers/dwf/oauth.php';
    private $cookieFilename = 'cookie.txt';
    private $WID;

    public function __construct(
        Curl $curl,
        Configuration $configuration,
        JoomlaGateway $joomlaGateway
    ) {
        $this->curl = $curl;
        $this->joomlaGateway = $joomlaGateway;
        $this->credentials = new Credentials($configuration->DwfUsername, $configuration->DwfPassword);

        if (file_exists($this->cookieFilename)) {
            $this->WID = file_get_contents($this->cookieFilename);
        }

        $this->Connect();
    }

    private function Connect(): void
    {
        $request = new Request($this->dwfOAuthUrl);
        $response = $this->curl->SendRequest($request);
        $locationHeader = $response->GetLocationHeader();
        $setCookieHeader = $response->GetSetCookieHeader();
        $this->WID = $this->GetWid($setCookieHeader);

        $request = new Request($locationHeader);
        $response = $this->curl->SendRequest($request);
        $setCookieHeader = $response->GetSetCookieHeader();
        $sessionId = $this->GetSessionId($setCookieHeader);

        $request = new Request('https://login.nevobo.nl/login_check');
        $request->SetHeaders(["Cookie: $sessionId"]);
        $body = [
            "_username" => $this->credentials->username,
            "_password" => $this->credentials->password
        ];
        $request->SetBody($body);
        $response = $this->curl->SendRequest($request);

        $locationHeader = $response->GetLocationHeader();
        $request = new Request("https://login.nevobo.nl" . $locationHeader);
        $sessionId = $this->GetSessionId($response->GetSetCookieHeader());
        $request->SetHeaders(["Cookie: $sessionId"]);
        $response = $this->curl->SendRequest($request);

        $locationHeader = $response->GetLocationHeader();
        $request = new Request($locationHeader);
        $request->SetHeaders(["Cookie: $this->WID"]);
        $this->curl->SendRequest($request);

        $fp = fopen($this->cookieFilename, 'w');
        fwrite($fp, $this->WID);
        fclose($fp);
    }

    public function GetSessionId(string $header): ?string
    {
        if (preg_match('/(PHPSESSID=\S*);/', $header, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }

    public function GetWid(string $header): ?string
    {
        if (preg_match('/(WID=\S*);/', $header, $matches)) {
            return $matches[1];
        } else {
            return null;
        }
    }

    public function GetGespeeldeWedstrijden(int $aantal = 999): array
    {
        $request = new Request($this->dwfUrl);
        $request->headers = [
            "Cookie: $this->WID",
        ];
        $request->body = [
            'type' => 'matchResults',
            'team' => '',
            'limit' => $aantal,
        ];

        $response = $this->curl->SendRequest($request);
        $data = json_decode($response->GetBody());

        if (!isset($data->results[0]->type)) {
            throw new UnexpectedValueException("Kan gespeelde wedstrijden niet ophalen: $response");
        }

        $wedstrijden = [];
        foreach ($data->results[0]->data as $item) {
            if ($item->type == 'item') {
                if ($item->data->sStartTime == '-') {
                    continue;
                }
                $wedstrijd = new DwfWedstrijd(
                    preg_replace('/\s+/', ' ', $item->data->sMatchId),
                    new Team($item->data->sHomeName),
                    new Team($item->data->sOutName),
                    $item->data->sStartTime[0],
                    $item->data->sStartTime[2]
                );
                $wedstrijden[] = $wedstrijd;
            }
        }

        return $wedstrijden;
    }

    private function IsPlayerRole(DOMElement $player, string $role)
    {
        return strpos($player->childNodes[1]->childNodes[0]->attributes[0]->value, $role) !== false;
    }

    private function IsPlayerPresent(DOMElement $player)
    {
        return strpos($player->childNodes[9]->childNodes[0]->attributes[1]->value, "checked") !== false;
    }

    private function GetIndexOfColumn(DOMElement $header, string $columnContent)
    {
        foreach ($header->childNodes as $i => $column) {
            $text = trim($column->textContent);
            if ($columnContent === $text) {
                return $i;
            }
        }

        throw new UnexpectedValueException("'$columnContent' is niet gevonden in header");
    }

    private function GetDwfPlayers(DOMNodeList $players): array
    {
        $header = $players[0];
        $rugnummerIndex = $this->GetIndexOfColumn($header, "Rugnr.");
        $naamIndex = $this->GetIndexOfColumn($header, "Naam");
        $relatiecodeIndex = $this->GetIndexOfColumn($header, "Relatiecode");

        $result = [];
        foreach ($players as $player) {
            if (count($player->attributes) > 0 && in_array($player->attributes[0]->value, ["header", "bottom", "remarkInput"])) {
                continue;
            }

            $rugnummer = $player->childNodes[$rugnummerIndex]->textContent;
            if (!is_numeric($rugnummer)) {
                throw new UnexpectedValueException("Rugnummer '$rugnummer' is geen getal");
            }
            $rugnummer = intval($rugnummer);
            $naam = trim($player->childNodes[$naamIndex]->textContent);
            $relatiecode = trim($player->childNodes[$relatiecodeIndex]->textContent);

            $newPlayer = new DwfSpeler($rugnummer, $naam, $relatiecode);
            $newPlayer->isCaptain = $this->IsPlayerRole($player, "captain");
            $newPlayer->isLibero = $this->IsPlayerRole($player, "libero");
            $newPlayer->isUitgekomen = $this->IsPlayerPresent($player);
            $result[] = $newPlayer;
        }

        return $result;
    }

    private function SetFormulierVariabelen(DwfWedstrijd $wedstrijd): void
    {
        $length = strlen($wedstrijd->matchId);
        $matchIdLength = 12;
        $replacement = str_repeat("%20", $matchIdLength - ($length - 1));
        $url = 'https://dwf.volleybal.nl/uitslagformulier/' . str_replace(' ', $replacement, $wedstrijd->matchId);
        $request = new Request($url);
        $request->headers = ["Cookie: $this->WID"];
        $response = $this->curl->SendRequest($request)->GetBody();
        $doc = new DOMDocument();

        libxml_use_internal_errors(true);
        @$doc->loadHTML($response);
        libxml_use_internal_errors(false);
        $selector = new DOMXPath($doc);

        $homeTeam = $selector->query("//div[contains(@class, 'home players')]/table/tr");
        $wedstrijd->team1->teamgenoten = $this->GetDwfPlayers($homeTeam);

        $awayTeam = $selector->query("//div[contains(@class, 'out players')]/table/tr");
        $wedstrijd->team2->teamgenoten = $this->GetDwfPlayers($awayTeam);

        if (preg_match_all('/<input type="hidden" id="iMatchFormId" value="(\d*)" name="iMatchFormId"/', $response, $output_array)) {
            $wedstrijd->formulierId = $output_array[1][0];
        } else {
            throw new UnexpectedValueException("Formulier ID kon niet worden gevonden");
        }
    }

    public function GetDwfPunten(DwfWedstrijd $wedstrijd): array
    {
        $request = new Request($this->dwfUrl);
        $request->headers = ["Cookie: $this->WID"];
        $request->body = [
            'type' => 'setProgression',
            'iNumberItems' => 8, // blijkbaar 8 = alle punten
            'sMatchId' => $wedstrijd->matchId,
            'sPageType' => 'resultForm',
            'iMatchFormId' => $wedstrijd->formulierId,
        ];
        $data = $this->curl->SendRequest($request)->GetBody();
        $data = json_decode($data);
        if ($data->error->code != 0) {
            throw new UnexpectedValueException('Kan wedstrijd verloop niet ophalen: ' . print_r($data, 1));
        }

        $result = [];
        $punten = $data->results[0]->data;
        $numberOfItems = count($punten);
        for ($i = $numberOfItems - 2; $i >= 0; $i--) {
            $result[] = $punten[$i]->data;
        }
        return $result;
    }

    public function AppendWedstrijdVerloop(DwfWedstrijd $wedstrijd): DwfWedstrijd
    {
        $this->SetFormulierVariabelen($wedstrijd);

        $currentSet = null;
        $punten = $this->GetDwfPunten($wedstrijd);
        foreach ($punten as $punt) {
            switch ($punt->sLogType) {
                case Punttype::RESTART_SET:
                    $wedstrijd->sets[] = new DwfSet();
                    $currentSet = $currentSet === null ? 0 : $currentSet + 1;
                    break;
                case Punttype::POINT:
                    $wedstrijd->sets[$currentSet]->punten[] = new DwfPunt(
                        $punt->iSetResultHomeTeam,
                        $punt->iSetResultOutTeam,
                        $punt->sTeam == "home" ? ThuisUit::THUIS : ThuisUit::UIT,
                        $punt->sPreviousServiceFor == "home" ? ThuisUit::THUIS : ThuisUit::UIT,
                        $punt->iPreviousServiceForShirtNr
                    );
                    break;
                case Punttype::TIME_OUT:
                    $wedstrijd->sets[$currentSet]->punten[] = new DwfTimeout(
                        $punt->iSetResultHomeTeam,
                        $punt->iSetResultOutTeam,
                        $punt->sTeam == "home" ? ThuisUit::THUIS : ThuisUit::UIT
                    );
                    break;
                case Punttype::SUBSTITUTION:
                    if (preg_match('/^(Uitzonderlijke spelerswissel|Spelerswissel): (\d*) voor (\d*) in het veld$/', $punt->sMessage, $output_array)) {
                        $bankspeler = intval($output_array[2]);
                        $veldspeler = intval($output_array[3]);
                        $team = $punt->sTeam == "home" ? ThuisUit::THUIS : ThuisUit::UIT;

                        $wedstrijd->sets[$currentSet]->punten[] = new DwfWissel(
                            $veldspeler,
                            $bankspeler,
                            $team
                        );

                        $isNewWissel = true;
                        foreach ($wedstrijd->sets[$currentSet]->{$team . "wissels"} as $uit => $in) {
                            if ($bankspeler == $uit && $veldspeler == $in) {
                                $isNewWissel = false;
                                break;
                            }
                        }
                        if ($isNewWissel) {
                            $wedstrijd->sets[$currentSet]->{$team . "wissels"}[$veldspeler] = $bankspeler;
                        }
                    }
                    break;
                case Punttype::TICKET:
                    if ($currentSet === null) {
                        break;
                    }
                    $wedstrijd->sets[$currentSet]->punten[] = new DwfKaart(
                        $punt->iSetResultHomeTeam,
                        $punt->iSetResultOutTeam,
                        $punt->sTeam == "home" ? ThuisUit::THUIS : ThuisUit::UIT,
                        $punt->sMessage
                    );
                    break;
                case Punttype::GAME_DELAY:
                    $wedstrijd->sets[$currentSet]->punten[] =  new DwfSpelophoud(
                        $punt->iSetResultHomeTeam,
                        $punt->iSetResultOutTeam,
                        $punt->sMessage
                    );
                    break;
                default:
                    break;
            }
        }

        $this->DetermineBeginopstelling($wedstrijd);

        return $wedstrijd;
    }

    private function DetermineBeginopstelling(DwfWedstrijd &$wedstrijd): void
    {
        $allSkcSpelers = $this->joomlaGateway->GetAllSpelers();
        $this->SetUserIdsOfSkcTeams($wedstrijd, $allSkcSpelers);

        foreach ($wedstrijd->sets as $currenSet => $set) {
            foreach ([ThuisUit::THUIS, ThuisUit::UIT] as $team) {
                $opstelling = new DwfOpstelling();
                $currentServeerder = null;
                $aantalDwfMomenten = 0;
                $numberOfServeerders = 0;
                foreach ($set->punten as $punt) {
                    if ($punt instanceof DwfPunt) {
                        $newServeerder = ($punt->serverendTeam === "thuis" ? $wedstrijd->team1 : $wedstrijd->team2)->GetSpelerByRugnummer($punt->serveerder);
                        if ($punt->serverendTeam == $team && $newServeerder !== null && !$newServeerder->IsEqual($currentServeerder)) {
                            $currentServeerder = $newServeerder;
                            $opstelling->SetSpeler($newServeerder, $numberOfServeerders++);
                        }
                    }
                    $aantalDwfMomenten++;
                    if ($numberOfServeerders == 6) {
                        break;
                    }
                }

                for ($i = $aantalDwfMomenten - 1; $i >= 0; $i--) {
                    $punt = $set->punten[$i];
                    if ($punt instanceof DwfWissel && $punt->team == $team) {
                        $wisselendeTeam = $punt->team === "thuis" ? $wedstrijd->team1 : $wedstrijd->team2;
                        $bankspeler = $wisselendeTeam->GetSpelerByRugnummer($punt->bankspeler);
                        $veldspeler = $wisselendeTeam->GetSpelerByRugnummer($punt->veldspeler);
                        $opstelling->WisselSpeler($bankspeler, $veldspeler);
                    }
                }

                foreach ($set->punten as $punt) {
                    if ($punt instanceof DwfPunt) {
                        if ($punt->serverendTeam !== $team) {
                            $opstelling->Terugdraaien();
                        }
                        break;
                    }
                }

                $wedstrijd->sets[$currenSet]->{$team . "opstelling"} = $opstelling;
            }
        }
    }

    private function SetUserIdsOfSkcTeams(DwfWedstrijd $wedstrijd, array $skcSpelers)
    {
        $teams = ["team1", "team2"];
        foreach ($teams as $team) {
            if ($wedstrijd->{$team}->IsSkcTeam()) {
                foreach ($wedstrijd->{$team}->teamgenoten as $teamgenoot) {
                    $i = array_search($teamgenoot->relatiecode, array_column($skcSpelers, 'relatiecode'));
                    if ($i === false) {
                        throw new UnexpectedValueException("Speler ('$teamgenoot->naam') met relatiecode '$teamgenoot->relatiecode' niet gevonden");
                    }
                    $teamgenoot->id = $skcSpelers[$i]->id;
                }
            }
        }
    }
}
