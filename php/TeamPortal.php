<?php

include 'Database.php';

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
        include 'UseCases' . DIRECTORY_SEPARATOR . 'MijnOverzicht' . DIRECTORY_SEPARATOR . 'GetMijnOverzicht.php';
        $database = new Database();
        $interactor = new GetMijnOverzichtInteractor($database);
        $interactor->Execute();
    }

    public function GetWedstrijdAanwezigheid()
    {
        include 'UseCases' . DIRECTORY_SEPARATOR . 'WedstrijdAanwezigheid' . DIRECTORY_SEPARATOR . 'GetWedstrijdAanwezigheid.php';
        $database = new Database();
        $interactor = new GetWedstrijdAanwezigheid($database);
        $interactor->Execute();
    }
}
