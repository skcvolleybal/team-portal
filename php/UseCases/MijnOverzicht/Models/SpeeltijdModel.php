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
        $this->isMogelijk = $speeltijd->isMogelijk;
        $this->isBeschikbaar = $speeltijd->isBeschikbaar;

        foreach ($speeltijd->wedstrijden as $wedstrijd) {
            $this->wedstrijden[] = new WedstrijdModel($wedstrijd);
        }
    }
}
