<?php

class CompleteDailyTasks implements Interactor
{
    public function __construct($database)
    {
        $this->database = $database;
    }

    public function Execute()
    {
        $result = [];
        // $setAllFluitbeschikbaarhedenInteractor = new SetAllFluitbeschikbaarheden($this->database);
        // $result[] = $setAllFluitbeschikbaarhedenInteractor->Execute();

        // $setAllBarcieBeschikbaarhedenInteractor = new SetAllBarcieBeschikbaarheden($this->database);
        // $result[] = $setAllBarcieBeschikbaarhedenInteractor->Execute();

        $generateTeamstandenInteractor = new GenerateTeamstanden();
        $result[] = $generateTeamstandenInteractor->Execute();

        $generateTeamoverzichtenInteractor = new GenerateTeamoverzichten($this->database);
        $result[] = $generateTeamoverzichtenInteractor->Execute();

        print_r($result);
    }
}
