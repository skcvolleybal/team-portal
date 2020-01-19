<?php

class Barciebeschikbaarheid extends Beschikbaarheid
{
    public Bardag $bardag;

    public function  __construct(Bardag $bardag, Persoon $persoon, ?bool $isBeschikbaar, int $id = null)
    {
        $this->bardag = $bardag;
        parent::__construct($id, $persoon, $bardag->date, $isBeschikbaar);
    }
}
