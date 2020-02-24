<?php

namespace TeamPortal\Common;

class Request
{
    public string $url;
    public array $headers = [];
    public $body = null;

    public function __construct(string $url)
    {
        $this->url = $url;

        if (strpos($url, '?') !== false) {
            $this->url = $this->GetSanitizedQueryString();
        }
    }

    function GetSanitizedQueryString(): string
    {
        $queryparams = explode('?', $this->url);
        $oldParams = explode('&', $queryparams[1]);
        $newParams = [];
        foreach ($oldParams as $oldParam) {
            $keyValuePair = explode('=', $oldParam);
            $newParams[] = $keyValuePair[0] . '=' . rawurlencode(rawurldecode($keyValuePair[1]));
        }
        return $queryparams[0] . '?' . implode('&', $newParams);
    }

    public function SetHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function SetBody(array $body)
    {
        $this->body = $body;
    }
}
