<?php

class DwfGateway
{
    private $dwfUrl = "https://dwf.volleybal.nl/application/handlers/dwf/pull/";

    public function __construct()
    {

    }

    public function Login()
    {

        $oauthPage = $this->SendHeadersRequest("https://dwf.volleybal.nl/application/handlers/dwf/oauth.php");
        $location = $this->SanitizeQueryString($oauthPage['Location']);
        $WID = substr($oauthPage['Set-Cookie'], 0, strpos($oauthPage['Set-Cookie'], ";"));

        $loginPage = $this->SendHeadersRequest($location);
        $PHPSESSID = substr($loginPage['Set-Cookie'], 0, strpos($loginPage['Set-Cookie'], ";"));

        $loginCheck = $this->SendHeadersRequest("https://login.nevobo.nl/login_check", ["Cookie: $PHPSESSID"], "_username=XXXXXXXXXXXXXXX&_password=XXXXXXXXXX");
        $location = $loginCheck['Location'];
        $PHPSESSID = substr($loginCheck['Set-Cookie'], 0, strpos($loginCheck['Set-Cookie'], ";"));

        $codePage = $this->SendHeadersRequest($location, ["Cookie: $PHPSESSID"]);
        $location = $codePage['Location'];

        $codePage = $this->SendHeadersRequest($location, ["Cookie: $WID"]);
        $codePage = $this->SendHeadersRequest("https://dwf.volleybal.nl/", ["Cookie: $WID"]);
        exit;
    }

    private function SendHeadersRequest($url, $headers = null, $postFields = null)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);

        if ($postFields && !empty($postFields)) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
        }

        if ($headers != null) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($ch);
        $headers = $this->GetHeaders($response);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $headers;
    }

    public function GetHeaders($response)
    {
        $headers = array();

        $header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

        foreach (explode("\r\n", $header_text) as $i => $line) {
            if ($i === 0) {
                $headers['http_code'] = $line;
            } else {
                list($key, $value) = explode(': ', $line);

                if (!isset($headers[$key])) {
                    $headers[$key] = $value;
                } else {
                    if ($key == "Set-Cookie" && strpos($value, "PHPSESSID") !== false) {
                        $headers[$key] = $value;
                    }
                }
            }
        }

        return $headers;
    }

    public function GetGespeeldeWedstrijden($aantal = 20)
    {
        $body = [
            "type" => "matchResults",
            "team" => "",
            "limit" => $aantal,
        ];
        $headers = [
            "Cookie: $this->cookie",
        ];
        $response = SendPost($this->dwfUrl, $body, $headers);
        $data = json_decode($response);

        if (!isset($data->results[0]->type)) {
            InternalServerError("Kan gespeelde wedstrijden niet ophalen: $response");
        }

        $wedstrijden = [];
        foreach ($data->results[0]->data as $item) {
            if ($item->type == "item") {
                $wedstrijden[] = [
                    "id" => preg_replace('/\s+/', ' ', $item->data->sMatchId),
                    "team1" => $item->data->sHomeName,
                    "team2" => $item->data->sOutName,
                    "setsTeam1" => $item->data->sStartTime[0],
                    "setsTeam2" => $item->data->sStartTime[2],
                ];
            }
        }

        return $wedstrijden;
    }

    public function GetMatchFormId($matchId)
    {
        $url = "https://dwf.volleybal.nl/uitslagformulier/" . str_replace(" ", "%20%20%20", $matchId);
        $headers = [
            "Cookie: $this->cookie",
        ];
        $response = SendPost($url, null, $headers);
        $content = file_get_contents($url);

        if (preg_match('/<input type="hidden" id="iMatchFormId" value="(\d*)" name="iMatchFormId">/', $content, $output_array)) {
            return $output_array[1];
        }
    }

    public function GetWedstrijdVerloop($matchId)
    {
        $body = [
            "type" => "setProgression",
            "iNumberItems" => 8, // blijkbaar 8 = alle punten
            "sMatchId" => $matchId,
            "sPageType" => "resultForm",
            "iMatchFormId" => $this->GetMatchFormId($matchId),
        ];
        $headers = [
            "Cookie: $this->cookie",
        ];
        $response = SendPost($this->dwfUrl, $body, $headers);
        $data = json_decode($response);

        if ($data->error->code != 0) {
            InternalServerError("Kan wedstrijd verloop niet ophalen: " . print_r($data, 1));
        }

        $currentSetIndex = -1;
        $wissels = [];
        $numberOfItems = count($data->results[0]->data);
        for ($i = $numberOfItems - 2; $i >= 0; $i--) {
            $item = $data->results[0]->data[$i]->data;
            $type = $item->sLogType;
            if ($type == "point") {
                $result[$currentSetIndex]["punten"][] = [
                    "stand" => $item->iSetResultHomeTeam . " - " . $item->iSetResultOutTeam,
                    "isThuispunt" => $item->sTeam == "home",
                ];

                if ($serverendTeam != $item->sPreviousServiceFor) {
                    if ($serverendTeam == "home") {
                        $homeIndex++;
                    } else {
                        $awayIndex++;
                    }
                    $serverendTeam = $item->sPreviousServiceFor;
                }

                if ($item->sPreviousServiceFor == "home" && $homeIndex < 6 && $result[$currentSetIndex]['thuis'][$homeIndex] == null) {
                    $result[$currentSetIndex]['thuis'][$homeIndex] = $item->iPreviousServiceForShirtNr;
                }
                if ($item->sPreviousServiceFor == "out" && $awayIndex < 6 && $result[$currentSetIndex]['uit'][$awayIndex] == null) {
                    $result[$currentSetIndex]['uit'][$awayIndex] = $item->iPreviousServiceForShirtNr;
                }
            } else if ($type == "substitution") {
                if (preg_match('/Spelerswissel: (\d*) voor (\d*) in het veld/', $item->sMessage, $output_array)) {
                    $newWissel = [
                        "isThuisWissel" => $item->sTeam == "home",
                        "in" => intval($output_array[1]),
                        "uit" => intval($output_array[2]),
                    ];
                    $addWissel = true;
                    foreach ($wissels as $j => $wissel) {
                        if ($wissel['isThuisWissel'] == $newWissel['isThuisWissel'] && $newWissel['in'] == $wissel['uit']) {
                            $addWissel = false;
                            unset($wissels[$j]);
                            break;
                        }
                    }
                    if ($addWissel) {
                        $wissels[] = $newWissel;
                    }
                }
            } else if ($type == "restartSet") {
                foreach ($wissels as $wissel) {
                    for ($j = 0; $j < 6; $j++) {
                        if ($wissel['isThuisWissel']) {
                            if ($result[$currentSetIndex]["thuis"][$j] == $wissel['in'] || $result[$currentSetIndex]["thuis"][$j] == $wissel['uit']) {
                                $result[$currentSetIndex]["thuis"][$j] = $wissel['uit'];
                                break;
                            }
                        } else {
                            if ($result[$currentSetIndex]["uit"][$j] == $wissel['in'] || $result[$currentSetIndex]["uit"][$j] == $wissel['uit']) {
                                $result[$currentSetIndex]["uit"][$j] = $wissel['uit'];
                                break;
                            }
                        }
                    }
                }
                $wissels = [];

                for ($j = $i - 1; $j >= 0; $j--) {
                    $item = $data->results[0]->data[$j]->data;
                    if ($item->sLogType == "point") {
                        $serverendTeam = $item->sPreviousServiceFor;
                        break;
                    }
                }

                $homeIndex = 0;
                $awayIndex = 0;
                $currentSetIndex++;
                $result[] = [
                    "thuis" => [null, null, null, null, null, null],
                    "uit" => [null, null, null, null, null, null],
                    "punten" => [],
                ];
            } else if ($type == "timeOut") {

            } else {
                echo "";
            }
        }

        return $result;
    }
}
