<?php

class Request
{
    public string $url;
    public array $headers = [];
    public $body = null;
    public bool $receiveHeaders;

    public function __construct(string $url, bool $receiveHeaders = false)
    {
        $this->receiveHeaders = $receiveHeaders;
        $this->url = $url;
    }
}
