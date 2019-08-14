<?php

include_once 'Database.php';
include_once 'Utilities.php';

class TeamPortal
{
    public function __construct()
    {
        $configs = include('configuration.php');
        $config = $configs->database;

        $this->database = new Database(
            $config->host,
            $config->database,
            $config->username,
            $config->password,
            $config->options
        );
    }

    public function NoAction()
    {
        throw new UnexpectedValueException("No action specified");
    }

    public function UnknownAction($action)
    {
        throw new UnexpectedValueException("Unknown function '$action'");
    }

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

    public function GetCurrentUser()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Inloggen' . DIRECTORY_SEPARATOR . 'GetCurrentUser.php';
        $interactor = new GetCurrentUser($this->database);
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
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Aanwezigheid' . DIRECTORY_SEPARATOR . 'GetWedstrijdOverzicht.php';
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

    public function GetCoachAanwezigheid()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Aanwezigheid' . DIRECTORY_SEPARATOR . 'GetCoachAanwezigheid.php';
        $interactor = new GetCoachAanwezigheid($this->database);
        $interactor->Execute();
    }

    public function UpdateCoachAanwezigheid()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Aanwezigheid' . DIRECTORY_SEPARATOR . 'UpdateCoachAanwezigheid.php';
        $interactor = new UpdateCoachAanwezigheid($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetBarcieBeschikbaarheid()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Barcie' . DIRECTORY_SEPARATOR . 'GetBarcieBeschikbaarheid.php';
        $interactor = new GetBarcieBeschikbaarheid($this->database);
        $interactor->Execute();
    }

    public function WedstrijdenImporteren()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'DWF' . DIRECTORY_SEPARATOR . 'WedstrijdenImporteren.php';
        $interactor = new WedstrijdenImporteren($this->database);
        $interactor->Execute();
    }

    public function GetVoorpaginaRooster()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'WedstrijdOverzicht' . DIRECTORY_SEPARATOR . 'GetVoorpaginaRooster.php';
        $interactor = new GetVoorpaginaRooster($this->database);
        $interactor->Execute();
    }

    public function GenerateVoorpaginaRooster()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'ScheduledTasks' . DIRECTORY_SEPARATOR . 'GenerateVoorpaginaRooster.php';
        $interactor = new GenerateVoorpaginaRooster($this->database);
        print_r($interactor->Execute());
    }

    public function SetAllFluitBeschikbaarheden()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'ScheduledTasks' . DIRECTORY_SEPARATOR . 'SetAllFluitbeschikbaarheden.php';
        $interactor = new SetAllFluitbeschikbaarheden($this->database);
        print_r($interactor->Execute());
    }

    public function SetAllBarcieBeschikbaarheden()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'ScheduledTasks' . DIRECTORY_SEPARATOR . 'SetAllBarcieBeschikbaarheden.php';
        $interactor = new SetAllBarcieBeschikbaarheden($this->database);
        print_r($interactor->Execute());
    }

    public function GenerateTeamstanden()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'ScheduledTasks' . DIRECTORY_SEPARATOR . 'GenerateTeamstanden.php';
        $interactor = new GenerateTeamstanden($this->database);
        print_r($interactor->Execute());
    }

    public function GenerateTeamoverzichten()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'ScheduledTasks' . DIRECTORY_SEPARATOR . 'GenerateTeamoverzichten.php';
        $interactor = new GenerateTeamoverzichten($this->database);
        print_r($interactor->Execute());
    }

    public function CompleteDailyTasks()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'ScheduledTasks' . DIRECTORY_SEPARATOR . 'CompleteDailyTasks.php';
        $queryParameters = GetQueryParameters();
        $interactor = new CompleteDailyTasks($this->database);
        $interactor->Execute($queryParameters);
    }

    public function GetTeamstanden()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Teamstanden' . DIRECTORY_SEPARATOR . 'GetTeamstanden.php';
        $queryParameters = GetQueryParameters();
        $interactor = new GetTeamstanden();
        $interactor->Execute($queryParameters);
    }

    public function GetTeamoverzicht()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Teamstanden' . DIRECTORY_SEPARATOR . 'GetTeamoverzicht.php';
        $queryParameters = GetQueryParameters();
        $interactor = new GetTeamoverzicht();
        $interactor->Execute($queryParameters);
    }

    public function UpdateBarcieBeschikbaarheid()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Barcie' . DIRECTORY_SEPARATOR . 'UpdateBarcieBeschikbaarheid.php';
        $postData = GetPostValues();
        $interactor = new UpdateBarcieBeschikbaarheid($this->database);
        $interactor->Execute($postData);
    }

    public function GetGroups()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Inloggen' . DIRECTORY_SEPARATOR . 'GetGroups.php';
        $interactor = new GetGroups($this->database);
        $interactor->Execute();
    }

    public function GetBarcieBeschikbaarheden()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Barcie' . DIRECTORY_SEPARATOR . 'GetBarcieBeschikbaarheden.php';
        $queryParameters = GetQueryParameters();
        $interactor = new GetBarcieBeschikbaarheden($this->database);
        $interactor->Execute($queryParameters);
    }

    public function AddBarcieAanwezigheid()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Barcie' . DIRECTORY_SEPARATOR . 'AddBarcieAanwezigheid.php';
        $postData = GetPostValues();
        $interactor = new AddBarcieAanwezigheid($this->database);
        $interactor->Execute($postData);
    }

    public function DeleteBarcieAanwezigheid()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Barcie' . DIRECTORY_SEPARATOR . 'DeleteBarcieAanwezigheid.php';
        $postData = GetPostValues();
        $interactor = new DeleteBarcieAanwezigheid($this->database);
        $interactor->Execute($postData);
    }

    public function GetBarcieRooster()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Barcie' . DIRECTORY_SEPARATOR . 'GetBarcieRooster.php';
        $interactor = new GetBarcieRooster($this->database);
        $interactor->Execute();
    }

    public function ToggleBhv()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Barcie' . DIRECTORY_SEPARATOR . 'ToggleBhv.php';
        $postData = GetPostValues();
        $interactor = new ToggleBhv($this->database);
        $interactor->Execute($postData);
    }

    public function AddBarcieDag()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Barcie' . DIRECTORY_SEPARATOR . 'AddBarcieDag.php';
        $postData = GetPostValues();
        $interactor = new AddBarcieDag($this->database);
        $interactor->Execute($postData);
    }

    public function DeleteBarcieDag()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'Barcie' . DIRECTORY_SEPARATOR . 'DeleteBarcieDag.php';
        $postData = GetPostValues();
        $interactor = new DeleteBarcieDag($this->database);
        $interactor->Execute($postData);
    }

    public function GetGespeeldePunten()
    {
        include_once 'UseCases' . DIRECTORY_SEPARATOR . 'DWF' . DIRECTORY_SEPARATOR . 'GetGespeeldePunten.php';
        $postData = GetPostValues();
        $interactor = new GetGespeeldePunten($this->database);
        exit(json_encode($interactor->Execute($postData)));
    }
}
