<?php

namespace TeamPortal\Entities;

class Spelsysteem
{
    public const VIJF_EEN = "5-1";
    public const VIER_TWEE = "4-2";
    
    public string $type;
    public int $totaalAantalPunten = 0;

    public function __construct(string $type)
    {
        $this->type = $type;
    }
}
