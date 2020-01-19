<?php

class SpeeltijdModel
{
    public string $tijd;
    public array $wedstrijden = [];
    public ?bool $isBeschikbaar;
    public ?bool $isMogelijk;

    public function __construct(Speeltijd $speeltijd)
    {
        $this->tijd = DateFunctions::GetTime($speeltijd->time);        
    }
}
