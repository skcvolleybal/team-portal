<?php

class Persoon
{
    public int $id;
    public string $naam;
    public ?string $email;

    function __construct(int $id, string $naam, $email = null)
    {
        $this->id = $id;
        $this->naam = $naam;
        $this->email = $email;
    }
}
