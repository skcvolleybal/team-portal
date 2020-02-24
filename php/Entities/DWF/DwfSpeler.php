<?php

namespace TeamPortal\Entities;

class DwfSpeler extends Persoon
{
    public string $relatiecode;
    public ?string $geboortedatum;
    public bool $isUitgekomen;

    public function __construct(int $rugnummer, string $naam, string $relatiecode)
    {
        $this->rugnummer = $rugnummer;
        $this->naam = $naam;
        $this->relatiecode = $relatiecode;
    }

    public function IsEqual(?DwfSpeler $speler)
    {
        if ($speler === null) {
            return false;
        }
        return $this->relatiecode === $speler->relatiecode;
    }
}
