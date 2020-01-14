<?php

class BeschikbareScheidsrechters
{
    public string $type;
    public array $ja = [];
    public array $onbekend = [];
    public array $nee = [];

    private $types = ["spelendeScheidsrechters", "overigeScheidsrechters"];

    public function __construct($type)
    {
        if (!in_array($type, $this->types)) {
            throw new InvalidArgumentException("$type is niet een van de opties");
        }

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
