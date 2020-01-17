<?php

class Bardienst
{
    public ?int $id;
    public Bardag $bardag;
    public ?Persoon $persoon;
    public ?int $shift;
    public ?bool $isBhv;

    public function __construct(Bardag $bardag, ?Persoon $persoon, ?int $shift, ?bool $isBhv, int $id = null)
    {
        $this->id = $id;
        $this->bardag = $bardag;
        $this->persoon = $persoon;
        $this->shift = $shift;
        $this->isBhv = $isBhv;
    }
}
