<?php

namespace TeamPortal\UseCases;

class BarbeschikbaarheidModel
{
    public string $datum;
    public string $date;
    public ?bool $beschikbaarheid;
    public array $eigenWedstrijden;
    public ?bool $isMogelijk;
}
