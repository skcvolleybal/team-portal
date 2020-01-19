<?php

class Headers
{
    public const LOCATION = 'Location';
    public const SET_COOKIE = 'Set-Cookie';
}

class DwfGateway
{
    private $dwfUrl = 'https://dwf.volleybal.nl/application/handlers/dwf/pull/';
    private $dwfOAuthUrl = 'https://dwf.volleybal.nl/application/handlers/dwf/oauth.php';
    private $cookieFilename = 'cookie.txt';
    private $WID;

    public function __construct(
        CurlGateway $curlGateway,
        Configuration $configuration
    ) {
        $this->curlGateway = $curlGateway;
        $this->credentials = new Credentials($configuration->DwfUsername, $configuration->DwfPassword);

        if (file_exists($this->cookieFilename)) {
            $this->WID = file_get_contents($this->cookieFilename);
        }

        $this->Connect();
    }

    private function asd()
    {
        $oauthPage = $this->SendHeadersRequest($this->dwfOAuthUrl);
        $this->WID = $this->GetCookieValueFromHeader($oauthPage['Set-Cookie']);

        $location = SanitizeQueryString($oauthPage['Location']);
        $loginPage = $this->SendHeadersRequest($location);

        $sessionId = $this->GetCookieValueFromHeader($loginPage['Set-Cookie']);
        $data = (object) [
            '_username' => $this->username,
            '_password' => $this->password,
        ];
        $headers = ["Cookie: $sessionId"];
        $url = 'https://login.nevobo.nl/login_check';
        $loginCheck = $this->SendHeadersRequest($url, $headers, $data);

        $location = "https://login.nevobo.nl" . $loginCheck['Location'];
        $sessionId = $this->GetCookieValueFromHeader($loginCheck['Set-Cookie']);
        $codePage = $this->SendHeadersRequest($location, ["Cookie: $sessionId"]);

        $location = $codePage['Location'];
        $this->SendHeadersRequest($location, ["Cookie: $this->WID"]);
    }

    private function Connect()
    {
        $request = new Request($this->dwfOAuthUrl);
        $response = $this->curlGateway->SendRequest($request);
        $headers = $this->curlGateway->GetHeaders($response);
        $location = $this->curlGateway->SanitizeQueryString($headers[HEADERS::LOCATION]);
        $this->WID = $this->GetWid($headers[HEADERS::SET_COOKIE]);

        $request = new Request($location);
        $response = $this->curlGateway->SendRequest($request);
        $headers = $this->curlGateway->GetHeaders($response);
        $sessionId = $this->GetSessionId($headers[HEADERS::SET_COOKIE]);

        $request = new Request('https://login.nevobo.nl/login_check');
        $request->headers = ["Cookie: $sessionId"];
        $request->body = ["_username" => $this->credentials->username, "_password" => $this->credentials->password];
        $response = $this->curlGateway->SendRequest($request);

        $headers = $this->curlGateway->GetHeaders($response);
        $location = "https://login.nevobo.nl" . $headers['Location'];
        $sessionId = $this->GetSessionId($headers['Set-Cookie']);
        $request = new Request($location);
        
        $request->headers = ["Cookie: PHPSESSID=$sessionId"];
        $response = $this->curlGateway->SendRequest($request);

        $headers = $this->curlGateway->GetHeaders($response);
        $request = new Request($headers[Headers::LOCATION]);
        $request->headers = ["Cookie: $this->WID"];
        $response = $this->curlGateway->SendRequest($request);

        $fp = fopen($this->cookieFilename, 'w');
        fwrite($fp, $this->WID);
        fclose($fp);
    }

    private function  GetSessionId(string $header): string
    {
        preg_match('/PHPSESSID=(\S*);/', $header, $matches);
        return $matches[1];
    }

    private function GetWid(string $header)
    {
        preg_match('/WID=(.*); path=\//', $header, $matches);
        return $matches[1];
    }

    public function GetGespeeldeWedstrijden($aantal = 999)
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

        $response = $this->curlGateway->SendRequest($request);
        $data = json_decode($response);

        if (!isset($data->results[0]->type)) {
            throw new UnexpectedValueException("Kan gespeelde wedstrijden niet ophalen: $response");
        }

        $wedstrijden = [];
        foreach ($data->results[0]->data as $item) {
            if ($item->type == 'title') {
                $date = $item->data->sDate;
            } else if ($item->type == 'item') {
                if ($item->data->sStartTime == '-') {
                    continue;
                }
                $wedstrijden[] = (object) [
                    'id' => preg_replace('/\s+/', ' ', $item->data->sMatchId),
                    'date' => $date,
                    'team1' => $item->data->sHomeName,
                    'team2' => $item->data->sOutName,
                    'setsTeam1' => $item->data->sStartTime[0],
                    'setsTeam2' => $item->data->sStartTime[2],
                ];
            }
        }

        return $wedstrijden;
    }

    public function GetMatchFormId($matchId)
    {
        $url = 'https://dwf.volleybal.nl/uitslagformulier/' . str_replace(' ', '%20%20%20', $matchId);
        $headers = (object) [
            "Cookie: $this->WID",
        ];
        $response = SendPost($url, null, $headers);

        if (preg_match_all('/<input type="hidden" id="iMatchFormId" value="(\d*)" name="iMatchFormId"/', $response, $output_array)) {
            return $output_array[1][0];
        }
    }

    private function GetWedstrijdVerloopData($matchId)
    {
        $body = (object) [
            'type' => 'setProgression',
            'iNumberItems' => 8, // blijkbaar 8 = alle punten
            'sMatchId' => $matchId,
            'sPageType' => 'resultForm',
            'iMatchFormId' => $this->GetMatchFormId($matchId),
        ];
        $headers = (object) [
            "Cookie: $this->WID",
        ];
        $response = SendPost($this->dwfUrl, $body, $headers);
        $data = json_decode($response);
        if ($data->error->code != 0) {
            throw new UnexpectedValueException('Kan wedstrijd verloop niet ophalen: ' . print_r($data, 1));
        }

        $match = (object) [
            "sets" => []
        ];
        $currentSet = -1;

        $numberOfItems = count($data->results[0]->data);
        for ($i = $numberOfItems - 2; $i >= 0; $i--) {
            $point = $data->results[0]->data[$i]->data;
            switch ($point->sLogType) {
                case "restartSet":
                    $match->sets[] = (object) [
                        "wissels" => (object) [
                            "thuis" => (object) [],
                            "uit" => (object) []
                        ],
                        "beginopstellingen" => (object) [
                            "thuis" => (object) [],
                            "uit" => (object) []
                        ],
                        "punten" => []
                    ];
                    $currentSet++;
                    break;
                case "point":
                    $match->sets[$currentSet]->punten[] = (object) [
                        "type" => "punt",
                        "scorendTeam" => $point->sTeam == "home" ? "thuis" : "uit",
                        "puntenThuisTeam" => $point->iSetResultHomeTeam,
                        "puntenUitTeam" => $point->iSetResultOutTeam,
                        "serverendTeam" => $point->sPreviousServiceFor == "home" ? "thuis" : "uit",
                        "serveerder" => $point->iPreviousServiceForShirtNr
                    ];

                    break;
                case "timeOut":
                    break;
                case "substitution":
                    if (preg_match('/^(Uitzonderlijke spelerswissel|Spelerswissel): (\d*) voor (\d*) in het veld$/', $point->sMessage, $output_array)) {
                        $spelerIn = intval($output_array[2]);
                        $spelerUit = intval($output_array[3]);
                        $team = $point->sTeam == "home" ? "thuis" : "uit";
                        $match->sets[$currentSet]->punten[] = (object) [
                            "type" => "wissel",
                            "spelerUit" => $spelerUit,
                            "spelerIn" => $spelerIn,
                            "team" => $team
                        ];

                        $isNewWissel = true;
                        foreach ($match->sets[$currentSet]->wissels->{$team} as $uit => $in) {
                            if ($spelerIn == $uit && $spelerUit == $in) {
                                $isNewWissel = false;
                                break;
                            }
                        }
                        if ($isNewWissel) {
                            $match->sets[$currentSet]->wissels->{$team}->{$spelerUit} = $spelerIn;
                        }
                    }
                    break;
                default:
                    $var = 1;
                    break;
            }
        }

        return $match;
    }

    private function DetermineBeginopstelling(&$wedstrijdverloop)
    {
        foreach ($wedstrijdverloop->sets as $currenSet => $set) {
            foreach (["thuis", "uit"] as $team) {
                $opstelling = [null, null, null, null, null, null];
                $serveerder = null;
                $aantalGespeeldePunten = 0;
                $numberOfServeerders = 0;
                foreach ($set->punten as $punt) {
                    if ($punt->type == "punt" && $punt->serverendTeam == $team && $serveerder != $punt->serveerder) {
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
                    if ($punt->type == "wissel" && $punt->team == $team) {
                        for ($j = 0; $j < 6; $j++) {
                            if ($opstelling[$j] == $punt->spelerIn) {
                                $opstelling[$j] = $punt->spelerUit;
                                break;
                            }
                        }
                    }
                }
                $wedstrijdverloop->sets[$currenSet]->beginopstellingen->{$team} = $opstelling;
            }
        }
    }

    public function GetWedstrijdVerloop($matchId)
    {
        $wedstrijdverloop = $this->GetWedstrijdVerloopData($matchId);
        if (count($wedstrijdverloop->sets) == 0) {
            return null;
        }

        $this->DetermineBeginopstelling($wedstrijdverloop);

        return $wedstrijdverloop;
    }
}
