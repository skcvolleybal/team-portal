<?php

class Team
{
    public $id;
    public $naam;
    public $teamgenoten;    

    public function __construct($id, $naam, $teamgenoten = [])
    {
        $this->id = $id;
        $this->naam = $naam;
        $this->teamgenoten = $teamgenoten;
    }
}
