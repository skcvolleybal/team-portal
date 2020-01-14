<?php

class Scheidsrechter extends Persoon
{
    public int $aantalGeflotenWedstrijden;
    public ?string $niveau;

    public function __construct(int $id, string $naam, ?string $niveau = null, int $aantalGeflotenWedstrijden = 0)
    {
        $this->niveau = $niveau;
        $this->aantalGeflotenWedstrijden = $aantalGeflotenWedstrijden;

        parent::__construct($id, $naam);
    }
}
