<?php

include 'Database.php';

class TeamPortal
{
    private $database;

    public function __construct()
    {
        $this->database = new Database();
    }
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
        $interactor = new GetMijnOverzichtInteractor($this->database);
        $interactor->Execute();
    }

    public function GetWedstrijdAanwezigheid()
    {
        include 'UseCases' . DIRECTORY_SEPARATOR . 'Aanwezigheid' . DIRECTORY_SEPARATOR . 'GetWedstrijdAanwezigheid.php';
        $interactor = new GetWedstrijdAanwezigheid($this->database);
        $interactor->Execute();
    }

    public function UpdateAanwezigheid()
    {
        include 'UseCases' . DIRECTORY_SEPARATOR . 'Aanwezigheid' . DIRECTORY_SEPARATOR . 'UpdateAanwezigheid.php';
        $interactor = new UpdateAanwezigheid($this->database);
        $postData = $this->GetPostValues();
        $interactor->Execute($postData);
    }

    public function Login(){
      include 'UseCases' . DIRECTORY_SEPARATOR . 'Inloggen' . DIRECTORY_SEPARATOR . 'Inloggen.php';
      $interactor = new Inloggen($this->database);
      $postData = $this->GetPostValues();
      $interactor->Execute($postData);
    }

    private function GetPostValues()
    {
        $postData = file_get_contents("php://input");
        if (empty($postData)) {
            return null;
        }

        return json_decode($postData);
    }
}
