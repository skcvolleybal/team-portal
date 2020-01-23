<?php

class CompleteDailyTasks implements Interactor
{
    public function __construct(
        NevoboGateway $nevoboGateway,
        JoomlaGateway $joomlaGateway
    ) {
        $this->nevoboGateway = $nevoboGateway;
        $this->joomlaGateway = $joomlaGateway;
    }

    public function Execute(object $data = null)
    {
        $result = [];

        $generateTeamstandenInteractor = new GenerateTeamstanden($this->nevoboGateway);
        $result[] = $generateTeamstandenInteractor->Execute();

        $generateTeamoverzichtenInteractor = new GenerateTeamoverzichten($this->nevoboGateway, $this->joomlaGateway);
        $result[] = $generateTeamoverzichtenInteractor->Execute();

        print_r($result);
    }
}
