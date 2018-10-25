<?php
include_once '';

include 'IInteractor.php';
include_once 'GetNevoboMatchByDate';

class GetBarcieBeschikbaarheid extends GetNevoboMatchByDate implements IInteractor
{
    private $nevoboGateway;
    private $barcieGateway;
    private $joomlaGateway;

    public function __construct($database)
    {
        $this->nevoboGateway = new NevoboGateway();
        $this->barcieGateway = new BarcieGateway($database);
        $this->joomlaGateway = new JoomlaGateway($database);
    }

    public function Execute()
    {
        $userId = $this->joomlaGateway->GetUserId();

        if ($userId === null) {
            UnauthorizedResult();
        }

        $this->team = $this->joomlaGateway->GetTeam($userId);
        $this->coachTeam = $this->joomlaGateway->GetCoachTeam($userId);

        $this->wedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);
        $this->coachWedstrijden = $this->nevoboGateway->GetProgrammaForTeam($team);
        
        $this->barcieDagen = $this->barcieGateway->GetBarcieDagen();

        $response = [];
        foreach ($this->$barcieDagen as $barcieDag){
            $eigenWedstrijden = array_filter($this->wedstrijden, function ($wedstrijd) use ($barcieDag) {
                return $wedstrijd['timestamp'] && $wedstrijd['timestamp']->format("Y-m-d") == $barcieDag;
            });

            $coachWedstrijden = array_filter($this->coachWedstrijden, function ($wedstrijd) use ($barcieDag) {
                return $wedstrijd['timestamp'] && $wedstrijd['timestamp']->format("Y-m-d") == $barcieDag;
            });

            $beschikbaarheid = 

            
            $response
        }
    }
}
