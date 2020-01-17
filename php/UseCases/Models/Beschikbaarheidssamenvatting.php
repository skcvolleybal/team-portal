<?php

class Beschikbaarheidssamenvatting
{
    public ?string $type;
    public array $ja = [];
    public array $onbekend = [];
    public array $nee = [];

    public function __construct(string $type = null)
    {
        $this->type = $type;
    }

    public function AddScheidsrechter($scheidsrechter)
    {
        if ($scheidsrechter->isBeschikbaar === null) {
            $this->onbekend[] = $scheidsrechter;
        } else if ($scheidsrechter->isBeschikbaar) {
            $this->ja[] = $scheidsrechter;
        } else {
            $this->nee[] = $scheidsrechter;
        }
    }
}
