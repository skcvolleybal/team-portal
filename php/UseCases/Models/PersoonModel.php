<?php

class PersoonModel
{
    public function __construct(Persoon $persoon)
    {
        $this->id = $persoon->id;
        $this->naam = $persoon->naam;
    }
}
