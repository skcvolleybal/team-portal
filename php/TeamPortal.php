<?php

class TeamPortal
{
    public function NoAction()
    {
        header("HTTP/1.1 500 Internal Server Error");
        echo "No action specified";
        exit();
    }

    public function UnknownAction($action)
    {
        header("HTTP/1.1 500 Internal Server Error");
        echo "Unknown function '$action'";
        exit();
    }

    public function GetMijnOverzicht()
    {
        include 'UseCases\MijnOverzicht\GetMijnOverzicht.php';
        $interactor = new GetMijnOverzichtInteractor();
        $interactor->Execute();
    }
}
