<?php

namespace TeamPortal\Gateways;

use TeamPortal\Common\Curl;
use TeamPortal\Common\Header;
use TeamPortal\Common\Request;
use TeamPortal\Configuration;
use TeamPortal\Entities\Credentials;
use TeamPortal\Entities\DwfKaart;
use TeamPortal\Entities\DwfPunt;
use TeamPortal\Entities\DwfSet;
use TeamPortal\Entities\DwfSpelophoud;
use TeamPortal\Entities\DwfTimeout;
use TeamPortal\Entities\DwfWedstrijd;
use TeamPortal\Entities\DwfWissel;
use TeamPortal\Entities\Punttype;
use TeamPortal\Entities\Team;
use TeamPortal\Entities\ThuisUit;

class DwfGateway
{
    private $dwfUrl = 'https://dwf.volleybal.nl/application/handlers/dwf/pull/';
    private $dwfOAuthUrl = 'https://dwf.volleybal.nl/application/handlers/dwf/oauth.php';
    private $cookieFilename = 'cookie.txt';
    private $WID;

    public function __construct(
        Curl $curl,
        Configuration $configuration
    ) {
        $this->curl = $curl;
        $this->credentials = new Credentials($configuration->DwfUsername, $configuration->DwfPassword);

        if (file_exists($this->cookieFilename)) {
            $this->WID = file_get_contents($this->cookieFilename);
        }

        $this->Connect();
    }

    private function Connect(): void
    {
        $request = new Request($this->dwfOAuthUrl, true);
        $response = $this->curl->SendRequest($request);
        $headers = $this->curl->GetHeaders($response);
        $location = $this->curl->SanitizeQueryString($headers[Header::LOCATION]);
        $this->WID = $this->GetWid($headers[Header::SET_COOKIE]);

        $request = new Request($location, true);
        $response = $this->curl->SendRequest($request);
        $headers = $this->curl->GetHeaders($response);
        $sessionId = $this->GetSessionId($headers[Header::SET_COOKIE]);

        $request = new Request('https://login.nevobo.nl/login_check', true);
        $request->headers = ["Cookie: $sessionId"];
        $request->body = ["_username" => $this->credentials->username, "_password" => $this->credentials->password];
        $response = $this->curl->SendRequest($request);

        $headers = $this->curl->GetHeaders($response);
        $location = "https://login.nevobo.nl" . $headers['Location'];
        $request = new Request($location, true);
        $sessionId = $this->GetSessionId($headers['Set-Cookie']);
        $request->headers = ["Cookie: $sessionId"];
        $response = $this->curl->SendRequest($request);

        $headers = $this->curl->GetHeaders($response);
        $request = new Request($headers[Header::LOCATION], true);
        $request->headers = ["Cookie: $this->WID"];
        $this->curl->SendRequest($request);

        $fp = fopen($this->cookieFilename, 'w');
        fwrite($fp, $this->WID);
        fclose($fp);
    }

    private function GetSessionId(string $header): string
    {
        preg_match('/(PHPSESSID=\S*);/', $header, $matches);
        return $matches[1];
    }

    private function GetWid(string $header): string
    {
        preg_match('/(WID=\S*);/', $header, $matches);
        return $matches[1];
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
        $data = json_decode($response);

        if (!isset($data->results[0]->type)) {
            throw new \UnexpectedValueException("Kan gespeelde wedstrijden niet ophalen: $response");
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

    public function GetMatchFormId(string $matchId): string
    {
        $length = strlen($matchId);
        $replacement = str_repeat("%20", 12 - ($length - 1)); // 12 is de standaard lengte van een dwf matchId
        $url = 'https://dwf.volleybal.nl/uitslagformulier/' . str_replace(' ', $replacement, $matchId);
        $request = new Request($url);
        $request->headers = ["Cookie: $this->WID"];
        $response = $this->curl->SendRequest($request);

        if (preg_match_all('/<input type="hidden" id="iMatchFormId" value="(\d*)" name="iMatchFormId"/', $response, $output_array)) {
            return $output_array[1][0];
        }
    }

    private function GetWedstrijdVerloopData(DwfWedstrijd $wedstrijd): DwfWedstrijd
    {
        $request = new Request($this->dwfUrl);
        $request->headers = ["Cookie: $this->WID"];
        $request->body = [
            'type' => 'setProgression',
            'iNumberItems' => 8, // blijkbaar 8 = alle punten
            'sMatchId' => $wedstrijd->matchId,
            'sPageType' => 'resultForm',
            'iMatchFormId' => $this->GetMatchFormId($wedstrijd->matchId),
        ];
        $response = $this->curl->SendRequest($request);
        $data = json_decode($response);
        if ($data->error->code != 0) {
            throw new \UnexpectedValueException('Kan wedstrijd verloop niet ophalen: ' . print_r($data, 1));
        }

        $currentSet = null;
        $numberOfItems = count($data->results[0]->data);
        for ($i = $numberOfItems - 2; $i >= 0; $i--) {
            $point = $data->results[0]->data[$i]->data;
            switch ($point->sLogType) {
                case Punttype::RESTART_SET:
                    $wedstrijd->sets[] = new DwfSet();
                    $currentSet = $currentSet === null ? 0 : $currentSet + 1;
                    break;
                case Punttype::POINT:
                    $wedstrijd->sets[$currentSet]->punten[] = new DwfPunt(
                        $point->iSetResultHomeTeam,
                        $point->iSetResultOutTeam,
                        $point->sTeam == "home" ? ThuisUit::THUIS : ThuisUit::UIT,
                        $point->sPreviousServiceFor == "home" ? ThuisUit::THUIS : ThuisUit::UIT,
                        $point->iPreviousServiceForShirtNr
                    );
                    break;
                case Punttype::TIME_OUT:
                    $wedstrijd->sets[$currentSet]->punten[] = new DwfTimeout(
                        $point->iSetResultHomeTeam,
                        $point->iSetResultOutTeam,
                        $point->sTeam == "home" ? ThuisUit::THUIS : ThuisUit::UIT
                    );
                    break;
                case Punttype::SUBSTITUTION:
                    if (preg_match('/^(Uitzonderlijke spelerswissel|Spelerswissel): (\d*) voor (\d*) in het veld$/', $point->sMessage, $output_array)) {
                        $spelerIn = intval($output_array[2]);
                        $spelerUit = intval($output_array[3]);
                        $team = $point->sTeam == "home" ? ThuisUit::THUIS : ThuisUit::UIT;

                        $wedstrijd->sets[$currentSet]->punten[] = new DwfWissel(
                            $spelerIn,
                            $spelerUit,
                            $team
                        );

                        $isNewWissel = true;
                        foreach ($wedstrijd->sets[$currentSet]->{$team . "wissels"} as $uit => $in) {
                            if ($spelerIn == $uit && $spelerUit == $in) {
                                $isNewWissel = false;
                                break;
                            }
                        }
                        if ($isNewWissel) {
                            $wedstrijd->sets[$currentSet]->{$team . "wissels"}[$spelerUit] = $spelerIn;
                        }
                    }
                    break;
                case Punttype::TICKET:
                    $wedstrijd->sets[$currentSet]->punten[] = new DwfKaart(
                        $point->iSetResultHomeTeam,
                        $point->iSetResultOutTeam,
                        $point->sTeam == "home" ? ThuisUit::THUIS : ThuisUit::UIT,
                        $point->sMessage
                    );
                    break;
                case Punttype::GAME_DELAY:
                    $wedstrijd->sets[$currentSet]->punten[] =  new DwfSpelophoud(
                        $point->iSetResultHomeTeam,
                        $point->iSetResultOutTeam,
                        $point->sMessage
                    );
                    break;
                default:
                    break;
            }
        }

        return $wedstrijd;
    }

    private function DetermineBeginopstelling(DwfWedstrijd &$wedstrijd): void
    {
        foreach ($wedstrijd->sets as $currenSet => $set) {
            foreach ([ThuisUit::THUIS, ThuisUit::UIT] as $team) {
                $opstelling = [null, null, null, null, null, null];
                $serveerder = null;
                $aantalGespeeldePunten = 0;
                $numberOfServeerders = 0;
                foreach ($set->punten as $punt) {
                    if ($punt instanceof DwfPunt && $punt->serverendTeam == $team && $serveerder != $punt->serveerder) {
                        $serveerder = $punt->serveerder;
                        $opstelling[$numberOfServeerders++] = $serveerder;
                    }
                    $aantalGespeeldePunten++;
                    if ($numberOfServeerders == 6) {
                        break;
                    }
                }

                for ($i = $aantalGespeeldePunten - 1; $i >= 0; $i--) {
                    $punt = $set->punten[$i];
                    if ($punt instanceof DwfWissel && $punt->team == $team) {
                        for ($j = 0; $j < 6; $j++) {
                            if ($opstelling[$j] == $punt->spelerIn) {
                                $opstelling[$j] = $punt->spelerUit;
                                break;
                            }
                        }
                    }
                }
                $wedstrijd->sets[$currenSet]->{$team . "opstelling"} = $opstelling;
            }
        }
    }

    public function GetWedstrijdVerloop(DwfWedstrijd $wedstrijd): ?DwfWedstrijd
    {
        $wedstrijdverloop = $this->GetWedstrijdVerloopData($wedstrijd);
        if (count($wedstrijdverloop->sets) == 0) {
            return null;
        }

        $this->DetermineBeginopstelling($wedstrijdverloop);

        return $wedstrijdverloop;
    }
}
