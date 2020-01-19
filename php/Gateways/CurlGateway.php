<?php

class Request
{
    public string $url;
    public array $headers = []; 
    public $body = null;

    public function __construct(string $url)
    {
        $this->url = $url;
    }
}

class Response
{
    public $headers;
    public $body;
}

class CurlGateway
{
    function SendRequest(Request $request): string
    {
        $timeout = 5;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request->url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

        if ($request->body) {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $request->body);
        }

        if ($request->headers) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $request->headers);
        }

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Error:' . curl_error($ch);
        }
        curl_close($ch);

        return $response;
    }

    public function GetHeaders(string $response)
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
                    if ($key == 'Set-Cookie' && strpos($value, 'PHPSESSID') !== false) {
                        $headers[$key] = $value;
                    }
                }
            }
        }

        return $headers;
    }

    function GetCookieValueFromHeader($header)
    {
        $semiColonPosition = strpos($header, ';') ?? strlen($header);
        return trim(substr($header, 0, $semiColonPosition));
    }

    function SanitizeQueryString($url)
    {
        $url = explode('?', $url);
        $parts = explode('&', $url[1]);
        $newParts = [];
        foreach ($parts as $part) {
            $params = explode('=', $part);
            $newParts[] = $params[0] . '=' . rawurlencode($params[1]);
        }
        return $url[0] . '?' . implode('&', $newParts);
    }
}
