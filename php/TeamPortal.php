<?php

include_once 'Database.php';
include_once 'Utilities.php';

class TeamPortal
{
    public function __construct($config)
    {
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
        include_once 'UseCases/Webcie/IsWebcie.php';
        $interactor = new IsWebcie($this->database);
        $interactor->Execute();
    }

    public function GetUsers()
    {
        include_once 'UseCases/Webcie/GetUsers.php';
        $interactor = new GetUsers($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetCurrentUser()
    {
        include_once 'UseCases/Inloggen/GetCurrentUser.php';
        $interactor = new GetCurrentUser($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetMijnOverzicht()
    {
        include_once 'UseCases/MijnOverzicht/GetMijnOverzicht.php';
        $interactor = new GetMijnOverzichtInteractor($this->database);
        $interactor->Execute();
    }

    public function GetWedstrijdAanwezigheid()
    {
        include_once 'UseCases/WedstrijdOverzicht/GetWedstrijdAanwezigheid.php';
        $interactor = new GetWedstrijdAanwezigheid($this->database);
        $interactor->Execute();
    }

    public function GetWedstrijdOverzicht()
    {
        include_once 'UseCases/WedstrijdOverzicht/GetWedstrijdOverzicht.php';
        $interactor = new GetWedstrijdOverzicht($this->database);
        $interactor->Execute();
    }

    public function UpdateAanwezigheid()
    {
        include_once 'UseCases/WedstrijdOverzicht/UpdateAanwezigheid.php';
        $interactor = new UpdateAanwezigheid($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetFluitOverzicht()
    {
        include_once 'UseCases/FluitBeschikbaarheid/GetFluitBeschikbaarheid.php';
        $interactor = new GetFluitBeschikbaarheid($this->database);
        $interactor->Execute();
    }

    public function UpdateFluitBeschikbaarheid()
    {
        include_once 'UseCases/FluitBeschikbaarheid/UpdateFluitBeschikbaarheid.php';
        $interactor = new UpdateFluitBeschikbaarheid($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetScheidscoOverzicht()
    {
        include_once 'UseCases/Scheidsco/GetScheidscoOverzicht.php';
        $interactor = new GetScheidscoOverzicht($this->database);
        $interactor->Execute();
    }

    public function GetScheidsrechters()
    {
        include_once 'UseCases/Scheidsco/GetScheidsrechters.php';
        $interactor = new GetScheidsrechters($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetTelTeams()
    {
        include_once 'UseCases/Scheidsco/GetTelTeams.php';
        $interactor = new GetTelTeams($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetZaalwachtTeams()
    {
        include_once 'UseCases/Scheidsco/GetZaalwachtTeams.php';
        $interactor = new GetZaalwachtTeams($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function UpdateScheidsrechter()
    {
        include_once 'UseCases/Scheidsco/UpdateScheidsrechter.php';
        $interactor = new UpdateScheidsrechter($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function UpdateTellers()
    {
        include_once 'UseCases/Scheidsco/UpdateTellers.php';
        $interactor = new UpdateTellers($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function UpdateZaalwacht()
    {
        include_once 'UseCases/Scheidsco/UpdateZaalwacht.php';
        $interactor = new UpdateZaalwacht($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function Login()
    {
        include_once 'UseCases/Inloggen/Inloggen.php';
        $interactor = new Inloggen($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function SendWeeklyEmails()
    {
        include_once 'UseCases/Email/SendWeeklyEmails.php';
        $interactor = new SendWeeklyEmails($this->database);
        $interactor->Execute();
    }

    public function GetCalendar()
    {
        include_once 'UseCases/Calendar/GetCalendar.php';
        $interactor = new GetCalendar($this->database);
        $interactor->Execute();
    }

    public function GetCoachAanwezigheid()
    {
        include_once 'UseCases/WedstrijdOverzicht/GetCoachAanwezigheid.php';
        $interactor = new GetCoachAanwezigheid($this->database);
        $interactor->Execute();
    }

    public function UpdateCoachAanwezigheid()
    {
        include_once 'UseCases/WedstrijdOverzicht/UpdateCoachAanwezigheid.php';
        $interactor = new UpdateCoachAanwezigheid($this->database);
        $postData = GetPostValues();
        $interactor->Execute($postData);
    }

    public function GetBarcieBeschikbaarheid()
    {
        include_once 'UseCases/Barcie/GetBarcieBeschikbaarheid.php';
        $interactor = new GetBarcieBeschikbaarheid($this->database);
        $interactor->Execute();
    }

    public function WedstrijdenImporteren()
    {
        include_once 'UseCases/DWF/WedstrijdenImporteren.php';
        $interactor = new WedstrijdenImporteren($this->database);
        $interactor->Execute();
    }

    public function GetDwfPunten()
    {
        $data = GetQueryParameters();
        include_once 'UseCases/DWF/GetDwfPunten.php';
        $interactor = new GetDwfPunten($this->database);
        exit(json_encode($interactor->Execute($data)));
    }

    public function GetVoorpaginaRooster()
    {
        include_once 'UseCases/WedstrijdOverzicht/GetVoorpaginaRooster.php';
        $interactor = new GetVoorpaginaRooster($this->database);
        $interactor->Execute();
    }

    public function GenerateVoorpaginaRooster()
    {
        include_once 'UseCases/ScheduledTasks/GenerateVoorpaginaRooster.php';
        $interactor = new GenerateVoorpaginaRooster($this->database);
        exit(json_encode($interactor->Execute()));
    }

    public function SetAllFluitBeschikbaarheden()
    {
        include_once 'UseCases/ScheduledTasks/SetAllFluitbeschikbaarheden.php';
        $interactor = new SetAllFluitbeschikbaarheden($this->database);
        print_r($interactor->Execute());
    }

    public function SetAllBarcieBeschikbaarheden()
    {
        include_once 'UseCases/ScheduledTasks/SetAllBarcieBeschikbaarheden.php';
        $interactor = new SetAllBarcieBeschikbaarheden($this->database);
        print_r($interactor->Execute());
    }

    public function GenerateTeamstanden()
    {
        include_once 'UseCases/ScheduledTasks/GenerateTeamstanden.php';
        $interactor = new GenerateTeamstanden($this->database);
        print_r($interactor->Execute());
    }

    public function GenerateTeamoverzichten()
    {
        include_once 'UseCases/ScheduledTasks/GenerateTeamoverzichten.php';
        $interactor = new GenerateTeamoverzichten($this->database);
        print_r($interactor->Execute());
    }

    public function CompleteDailyTasks()
    {
        include_once 'UseCases/ScheduledTasks/CompleteDailyTasks.php';
        $queryParameters = GetQueryParameters();
        $interactor = new CompleteDailyTasks($this->database);
        $interactor->Execute($queryParameters);
    }

    public function GetTeamstanden()
    {
        include_once 'UseCases/Teamstanden/GetTeamstanden.php';
        $queryParameters = GetQueryParameters();
        $interactor = new GetTeamstanden();
        $interactor->Execute($queryParameters);
    }

    public function GetTeamoverzicht()
    {
        include_once 'UseCases/Teamstanden/GetTeamoverzicht.php';
        $queryParameters = GetQueryParameters();
        $interactor = new GetTeamoverzicht();
        $interactor->Execute($queryParameters);
    }

    public function UpdateBarcieBeschikbaarheid()
    {
        include_once 'UseCases/Barcie/UpdateBarcieBeschikbaarheid.php';
        $postData = GetPostValues();
        $interactor = new UpdateBarcieBeschikbaarheid($this->database);
        $interactor->Execute($postData);
    }

    public function GetGroups()
    {
        include_once 'UseCases/Inloggen/GetGroups.php';
        $interactor = new GetGroups($this->database);
        $interactor->Execute();
    }

    public function GetBarcieBeschikbaarheden()
    {
        include_once 'UseCases/Barcie/GetBarcieBeschikbaarheden.php';
        $queryParameters = GetQueryParameters();
        $interactor = new GetBarcieBeschikbaarheden($this->database);
        $interactor->Execute($queryParameters);
    }

    public function AddBarcieAanwezigheid()
    {
        include_once 'UseCases/Barcie/AddBarcieAanwezigheid.php';
        $postData = GetPostValues();
        $interactor = new AddBarcieAanwezigheid($this->database);
        $interactor->Execute($postData);
    }

    public function DeleteBarcieAanwezigheid()
    {
        include_once 'UseCases/Barcie/DeleteBarcieAanwezigheid.php';
        $postData = GetPostValues();
        $interactor = new DeleteBarcieAanwezigheid($this->database);
        $interactor->Execute($postData);
    }

    public function GetBarcieRooster()
    {
        include_once 'UseCases/Barcie/GetBarcieRooster.php';
        $interactor = new GetBarcieRooster($this->database);
        $interactor->Execute();
    }

    public function ToggleBhv()
    {
        include_once 'UseCases/Barcie/ToggleBhv.php';
        $postData = GetPostValues();
        $interactor = new ToggleBhv($this->database);
        $interactor->Execute($postData);
    }

    public function AddBarcieDag()
    {
        include_once 'UseCases/Barcie/AddBarcieDag.php';
        $postData = GetPostValues();
        $interactor = new AddBarcieDag($this->database);
        $interactor->Execute($postData);
    }

    public function DeleteBarcieDag()
    {
        include_once 'UseCases/Barcie/DeleteBarcieDag.php';
        $postData = GetPostValues();
        $interactor = new DeleteBarcieDag($this->database);
        $interactor->Execute($postData);
    }

    public function GetGespeeldePunten()
    {
        include_once 'UseCases/DWF/GetGespeeldePunten.php';
        $postData = GetPostValues();
        $interactor = new GetGespeeldePunten($this->database);
        exit(json_encode($interactor->Execute($postData)));
    }
}
