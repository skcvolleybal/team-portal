<?php

namespace TeamPortal\UseCases;

class Beschikbaarheidssamenvatting
{
    public ?string $type;
    public array $Ja = [];
    public array $Onbekend = [];
    public array $Nee = [];

    public function __construct(string $type = null)
    {
        $this->type = $type;
    }

    public function AddScheidsrechter($scheidsrechter)
    {
        if ($scheidsrechter->isBeschikbaar === null) {
            $this->Onbekend[] = $scheidsrechter;
        } else if ($scheidsrechter->isBeschikbaar) {
            $this->Ja[] = $scheidsrechter;
        } else {
            $this->Nee[] = $scheidsrechter;
        }
    }
}
