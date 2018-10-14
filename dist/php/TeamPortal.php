<?php

include_once 'Database.php';
include_once 'Utilities.php';

class TeamPortal
{
    private $database;

    public function __construct()
    {
        $this->database = new Database();
    }

    public function NoAction()
    {
        InternalServerError("No action specified");
    }

    public function UnknownAction($action)
    {
        InternalServerError("Unknown function '$action'");
    }

    // public function LogError($errorMessage)
    // {
    //     try {
    //         throw new Exception;
    //         $trace = GenerateCallTrace();
    //         $query = "INSERT INTO log (application, error_message, call_stack)
    //                   VALUES ('TeamPortal', :errorMessage, :trace)";
    //         $params = [
    //             new Param(":errorMessage", $errorMessage, PDO::PARAM_STR),
    //             new Param(":trace", $trace, PDO::PARAM_STR),
    //         ];
    //         $result = $this->database->Execute($query, $params);
    //     } catch (Exception $ex) {
    //         // write to disk
    //         $my_file = 'errors/error-' . date("Y-m-d.H:i:s.u") . '.txt';
    //         $handle = fopen($my_file, 'w') or die('Cannot open file:  ' . $my_file);
    //         fwrite($handle, $ex);
    //     }
    // }

    public function IsWebcie()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Webcie' . DIRECTORY_SEPARATOR . 'IsWebcie.php';
        $interactor = new IsWebcie($this->database);
        $interactor->Execute();
    }

    public function GetUsers()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Webcie' . DIRECTORY_SEPARATOR . 'GetUsers.php';
        $interactor = new GetUsers($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetMijnOverzicht()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'MijnOverzicht' . DIRECTORY_SEPARATOR . 'GetMijnOverzicht.php';
        $interactor = new GetMijnOverzichtInteractor($this->database);
        $interactor->Execute();
    }

    public function GetWedstrijdAanwezigheid()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Aanwezigheid' . DIRECTORY_SEPARATOR . 'GetWedstrijdAanwezigheid.php';
        $interactor = new GetWedstrijdAanwezigheid($this->database);
        $interactor->Execute();
    }

    public function GetWedstrijdOverzicht()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'WedstrijdOverzicht' . DIRECTORY_SEPARATOR . 'GetWedstrijdOverzicht.php';
        $interactor = new GetWedstrijdOverzicht($this->database);
        $interactor->Execute();
    }

    public function UpdateAanwezigheid()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Aanwezigheid' . DIRECTORY_SEPARATOR . 'UpdateAanwezigheid.php';
        $interactor = new UpdateAanwezigheid($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetFluitOverzicht()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'FluitBeschikbaarheid' . DIRECTORY_SEPARATOR . 'GetFluitBeschikbaarheid.php';
        $interactor = new GetFluitBeschikbaarheid($this->database);
        $interactor->Execute();
    }

    public function UpdateFluitBeschikbaarheid()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'FluitBeschikbaarheid' . DIRECTORY_SEPARATOR . 'UpdateFluitBeschikbaarheid.php';
        $interactor = new UpdateFluitBeschikbaarheid($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetScheidscoOverzicht()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Scheidsco' . DIRECTORY_SEPARATOR . 'GetScheidscoOverzicht.php';
        $interactor = new GetScheidscoOverzicht($this->database);
        $interactor->Execute();
    }

    public function GetScheidsrechters()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Scheidsco' . DIRECTORY_SEPARATOR . 'GetScheidsrechters.php';
        $interactor = new GetScheidsrechters($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetTelTeams()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Scheidsco' . DIRECTORY_SEPARATOR . 'GetTelTeams.php';
        $interactor = new GetTelTeams($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetZaalwachtTeams()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Scheidsco' . DIRECTORY_SEPARATOR . 'GetZaalwachtTeams.php';
        $interactor = new GetZaalwachtTeams($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function UpdateScheidsrechter()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Scheidsco' . DIRECTORY_SEPARATOR . 'UpdateScheidsrechter.php';
        $interactor = new UpdateScheidsrechter($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function UpdateTellers()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Scheidsco' . DIRECTORY_SEPARATOR . 'UpdateTellers.php';
        $interactor = new UpdateTellers($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function UpdateZaalwacht()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Scheidsco' . DIRECTORY_SEPARATOR . 'UpdateZaalwacht.php';
        $interactor = new UpdateZaalwacht($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function Login()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Inloggen' . DIRECTORY_SEPARATOR . 'Inloggen.php';
        $interactor = new Inloggen($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function SendWeeklyEmails()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Email' . DIRECTORY_SEPARATOR . 'SendWeeklyEmails.php';
        $interactor = new SendWeeklyEmails($this->database);
        $interactor->Execute();
    }

    public function GetCalendar()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Calendar' . DIRECTORY_SEPARATOR . 'GetCalendar.php';
        $interactor = new GetCalendar($this->database);
        $interactor->Execute();
    }
}
