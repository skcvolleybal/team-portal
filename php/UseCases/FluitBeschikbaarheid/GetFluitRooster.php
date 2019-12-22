<?php

include_once 'IInteractor.php';
include_once 'NevoboGateway.php';
include_once 'FluitBeschikbaarheidGateway.php';

class GetFluitRooster implements IInteractor
{
    public function __construct($database)
    {
        $this->nevoboGateway = new NevoboGateway();
        $this->telFluitGateway = new TelFluitGateway($database);
    }

    public function Execute()
    {
        $upcomingMatches = $this->nevoboGateway->GetProgrammaForVereniging();
        $playedMatches = $this->nevoboGateway->GetUitslagenForVereniging();
        $allMatches = array_merge($upcomingMatches, $playedMatches);

        $scheidsrechters = $this->telFluitGateway->GetScheidsrechters();


        $result = (object) [
            "rooster" => $allMatches,
            "scheidsrechters" => $scheidsrechters
        ];
        exit(json_encode($result));
    }
}
