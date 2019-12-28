<?php

class Wedstrijd
{
    public $id;
    public $team1;
    public $team2;
    public $poule;
    public $timestamp;
    public $locatie;

    public function __construct($id, $team1, $team2, $poule, $timestamp, $locatie)
    {
        $this->id = $id;
        $this->team1 = $team1;
        $this->team2 = $team2;
        $this->poule = $poule;
        $this->timestamp = $timestamp;
        $this->locatie = $locatie;
    }
}
