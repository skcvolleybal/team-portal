<?php

class Barciebeschikbaarheid extends Beschikbaarheid
{
    public Bardag $bardag;

    public function  __construct(Bardag $bardag, Persoon $persoon, DateTime $date, ?bool $isBeschikbaar, int $id = null)
    {
        $this->bardag = $bardag;
        parent::__construct($id, $persoon, $date, $isBeschikbaar);
    }
}
