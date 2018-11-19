<?php
include_once 'IInteractor.php';
include_once 'FluitBeschikbaarheid' . DIRECTORY_SEPARATOR . 'SetAllFluitbeschikbaarheden.php';
include_once 'WedstrijdOverzicht' . DIRECTORY_SEPARATOR . 'GenerateVoorpaginaRooster.php';
include_once 'Teamstanden' . DIRECTORY_SEPARATOR . 'GenerateTeamstanden.php';
include_once 'Teamstanden' . DIRECTORY_SEPARATOR . 'GenerateTeamoverzichten.php';

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

        $generateTeamstandenInteractor = new GenerateTeamstanden();
        $result[] = $generateTeamstandenInteractor->Execute();

        $generateVoorpaginaRoosterInteractor = new GenerateVoorpaginaRooster($this->database);
        $result[] = $generateVoorpaginaRoosterInteractor->Execute();

        $generateTeamoverzichtenInteractor = new GenerateTeamoverzichten($this->database);
        $result[] = $generateTeamoverzichtenInteractor->Execute();

        exit(print_r($result));
    }
}
