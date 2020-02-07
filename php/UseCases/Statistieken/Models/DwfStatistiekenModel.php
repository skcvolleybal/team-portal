<?php

namespace TeamPortal\UseCases;

use TeamPortal\Entities\Spelsysteem;

class DwfStatistiekenModel
{
    public array $spelsystemen = [];
    public int $aantalPunten;
    public array $gespeeldePunten = [];
    public array $services = [];
    public array $plusminus = [];
    public array $plusminusAlleenVoor = [];
    public array $combinaties = [];
    public array $eigenCombinaties = [];

    public function __construct(array $spelers)
    {
        $spelsysteem = new Spelsysteem(Spelsysteem::VIJF_EEN);
        for ($i = 1; $i <= 6; $i++) {
            $spelsysteem->puntenPerRotatie[] = new PuntenModel("Rotatie $i");
            $spelsysteem->puntenPerRotatieEigenService[] = new PuntenModel("Rotatie $i");
            $spelsysteem->puntenPerRotatieServiceontvangst[] = new PuntenModel("Rotatie $i");
        }
        $this->spelsystemen[] = $spelsysteem;

        $spelsysteem = new Spelsysteem(Spelsysteem::VIER_TWEE);
        for ($i = 1; $i <= 3; $i++) {
            $spelsysteem->puntenPerRotatie[] = new PuntenModel("Rotatie $i");
            $spelsysteem->puntenPerRotatieEigenService[] = new PuntenModel("Rotatie $i");
            $spelsysteem->puntenPerRotatieServiceontvangst[] = new PuntenModel("Rotatie $i");
        }
        $this->spelsystemen[] = $spelsysteem;

        foreach ($spelers as $speler) {
            $plusminus = new PuntenModel("speler");
            $plusminus->rugnummer = $speler->rugnummer;
            $plusminus->naam = $speler->naam;
            $plusminus->afkorting = $speler->GetAfkorting();
            $plusminus->voornaam = $speler->GetEersteNaam();
            $this->plusminus[]  = clone $plusminus;

            $this->plusminusAlleenVoor[]  = clone $plusminus;

            $this->services[]  = clone $plusminus;
        }
    }
}
