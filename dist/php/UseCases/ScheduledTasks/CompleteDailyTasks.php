<?php
include_once 'IInteractor.php';
include_once 'SetAllFluitbeschikbaarheden.php';
include_once 'SetAllBarcieBeschikbaarheden.php';
include_once 'GenerateVoorpaginaRooster.php';
include_once 'GenerateTeamstanden.php';
include_once 'GenerateTeamoverzichten.php';

class CompleteDailyTasks implements IInteractor
{

    public function __construct($database)
    {
        $this->database = $database;
    }

    public function Execute()
    {
        $result = [];
        $setAllFluitbeschikbaarhedenInteractor = new SetAllFluitbeschikbaarheden($this->database);
        $result[] = $setAllFluitbeschikbaarhedenInteractor->Execute();

        $setAllBarcieBeschikbaarhedenInteractor = new SetAllBarcieBeschikbaarheden($this->database);
        $result[] = $setAllFluitbeschikbaarhedenInteractor->Execute();

        $generateTeamstandenInteractor = new GenerateTeamstanden();
        $result[] = $generateTeamstandenInteractor->Execute();

        $generateVoorpaginaRoosterInteractor = new GenerateVoorpaginaRooster($this->database);
        $result[] = $generateVoorpaginaRoosterInteractor->Execute();

        $generateTeamoverzichtenInteractor = new GenerateTeamoverzichten($this->database);
        $result[] = $generateTeamoverzichtenInteractor->Execute();

        print_r($result);
    }
}
