<?php

class Persoon
{
    public $id;
    public $naam;
    public $email;

    function __construct($id, $naam, $email)
    {
        $this->id = $id;
        $this->naam = $naam;
        $this->email = $email;
    }
}
