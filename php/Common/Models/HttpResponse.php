<?php

namespace TeamPortal\Common;

class HttpResponse
{
    public array $headers = [];
    private string $body;

    public function __construct(string $package)
    {
        if (strpos($package, "\r\n\r\n") !== false) {
            list($headers, $body) = explode("\r\n\r\n", $package);
        } else {
            $headers = "";
            $body = $package;
        }

        foreach (explode("\r\n", $headers) as $i => $line) {
            if ($i === 0) {
                $this->headers['http_code'] = $line;
            } else {
                list($key, $value) = explode(': ', $line);

                if (!isset($this->headers[$key])) {
                    $this->headers[$key] = $value;
                } else {
                    if ($key == 'Set-Cookie' && strpos($value, 'PHPSESSID') !== false) {
                        $this->headers[$key] = $value;
                    }
                }
            }
        }
        $this->body = $body;
    }

    public function GetHeader(string $name): ?string
    {
        foreach ($this->headers as $key => $value) {
            if ($key === $name) {
                return $value;
            }
        }

        return null;
    }

    public function GetLocationHeader()
    {
        return $this->GetHeader('Location');
    }

    public function GetSetCookieHeader()
    {
        return $this->GetHeader('Set-Cookie');
    }

    public function GetBody()
    {
        return $this->body;
    }
}
