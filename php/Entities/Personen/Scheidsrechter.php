<?php

class Scheidsrechter extends Persoon
{
    public int $aantalGeflotenWedstrijden;
    public ?string $niveau;

    public function __construct(Persoon $persoon, ?string $niveau = null, int $aantalGeflotenWedstrijden = 0)
    {
        $this->niveau = $niveau;
        $this->aantalGeflotenWedstrijden = $aantalGeflotenWedstrijden;

        parent::__construct($persoon->id, $persoon->naam, $persoon->email);
    }
}
